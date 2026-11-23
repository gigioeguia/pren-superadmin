<?php

declare(strict_types=1);

namespace PHPStanRules;

use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * Guard anti-objetos-en-sesión (spec-session-serialization-json-fase5,
 * Q7/Q11/Q12): la sesión corre bajo `serialization=json`, así que todo
 * payload escrito debe ser scalar|array|null — un objeto degradaría a
 * array plano silenciosamente y rompería al lector. `mixed` se rechaza
 * para forzar cast/guard explícito; el contenido de los arrays no se
 * revisa (se arman desde input en los gateways).
 *
 * Formas cubiertas: put/push/flash/now sobre Store y fachada Session,
 * el helper array session([...]) y RedirectResponse::with() (flash data
 * es data de sesión).
 */
final class SessionPayloadRule implements Rule
{
    private const STORE_WRITE_METHODS = ['put', 'push', 'flash', 'now'];

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof CallLike) {
            return [];
        }

        $errors = [];

        foreach ($this->extractPayloads($node, $scope) as $payload) {
            if (! $this->isAllowed($payload['type'])) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Session payload for key "%s" must be scalar or array under serialization=json (objects and mixed degrade silently).',
                    $payload['key'],
                ))
                    ->identifier('session.payload.scalarArray')
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * @return list<array{key: string, type: Type}>
     */
    private function extractPayloads(CallLike $node, Scope $scope): array
    {
        if ($node instanceof FuncCall) {
            return $this->fromSessionHelper($node, $scope);
        }

        if ($node instanceof StaticCall) {
            return $this->fromFacade($node, $scope);
        }

        if ($node instanceof MethodCall) {
            return $this->fromMethodCall($node, $scope);
        }

        return [];
    }

    /**
     * session(['key' => $value]).
     *
     * @return list<array{key: string, type: Type}>
     */
    private function fromSessionHelper(FuncCall $node, Scope $scope): array
    {
        if (! $node->name instanceof Name || $node->name->toString() !== 'session') {
            return [];
        }

        $array = $node->getArgs()[0]->value ?? null;

        return $array instanceof Array_ ? $this->fromArrayItems($array, $scope) : [];
    }

    /**
     * Session::put('key', $value) / Session::put([...]).
     *
     * @return list<array{key: string, type: Type}>
     */
    private function fromFacade(StaticCall $node, Scope $scope): array
    {
        $method = $node->name instanceof Identifier ? $node->name->toString() : '';

        if (! in_array($method, self::STORE_WRITE_METHODS, true)) {
            return [];
        }

        if (! $node->class instanceof Name || $scope->resolveName($node->class) !== 'Illuminate\Support\Facades\Session') {
            return [];
        }

        return $this->fromArgs($node->getArgs(), $scope);
    }

    /**
     * session()->put('key', $value), $request->session()->put([...])
     * y redirect()->with('key', $value).
     *
     * @return list<array{key: string, type: Type}>
     */
    private function fromMethodCall(MethodCall $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $method = $node->name->toString();

        // Fallback determinista cuando el helper session()/->session()
        // no infiere Store: el nombre del receiver decide.
        $isStoreReceiver = $this->isStoreType($scope->getType($node->var))
            || $this->isSessionNamedReceiver($node->var);

        if (in_array($method, self::STORE_WRITE_METHODS, true) && $isStoreReceiver) {
            return $this->fromArgs($node->getArgs(), $scope);
        }

        if ($method === 'with' && (new ObjectType(RedirectResponse::class))->isSuperTypeOf($scope->getType($node->var))->yes()) {
            return $this->fromArgs($node->getArgs(), $scope);
        }

        return [];
    }

    /**
     * put('key', $value) o put(['key' => $value]).
     *
     * @param  list<Arg>  $args
     * @return list<array{key: string, type: Type}>
     */
    private function fromArgs(array $args, Scope $scope): array
    {
        if ($args === []) {
            return [];
        }

        $first = $args[0]->value;

        if ($first instanceof Array_) {
            return $this->fromArrayItems($first, $scope);
        }

        $value = $args[1]->value ?? null;

        if ($value === null) {
            return [];
        }

        return [
            ['key' => $this->keyName($first), 'type' => $scope->getType($value)],
        ];
    }

    /**
     * @return list<array{key: string, type: Type}>
     */
    private function fromArrayItems(Array_ $array, Scope $scope): array
    {
        $payloads = [];

        foreach ($array->items as $item) {
            if (! $item instanceof ArrayItem) {
                continue;
            }

            $payloads[] = [
                'key' => $item->key instanceof String_ ? $item->key->value : '(dynamic)',
                'type' => $scope->getType($item->value),
            ];
        }

        return $payloads;
    }

    /**
     * Contrato (Q12): scalar|array|null top-level; `mixed` rechazado —
     * fuerza cast/guard explícito; arrays sin recursión.
     */
    private function isAllowed(Type $type): bool
    {
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $inner) {
                if (! $this->isAllowed($inner)) {
                    return false;
                }
            }

            return true;
        }

        if ($type instanceof MixedType) {
            return false;
        }

        return $type->isScalar()->yes() || $type->isArray()->yes() || $type->isNull()->yes();
    }

    private function isStoreType(Type $type): bool
    {
        return (new ObjectType(Store::class))->isSuperTypeOf($type)->yes();
    }

    private function isSessionNamedReceiver(mixed $receiver): bool
    {
        if ($receiver instanceof FuncCall && $receiver->name instanceof Name) {
            return $receiver->name->toString() === 'session';
        }

        return $receiver instanceof MethodCall
            && $receiver->name instanceof Identifier
            && $receiver->name->toString() === 'session';
    }

    private function keyName(mixed $node): string
    {
        return $node instanceof String_ ? $node->value : '(dynamic)';
    }
}
