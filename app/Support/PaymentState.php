<?php

namespace App\Support;

/**
 * Snapshot del estado de pago leído de la sesión web por el
 * PaymentController (spec-service-payment-fase5): el PaymentService es
 * agnóstico de HTTP/sesión y recibe este estado ya tipado; el
 * controlador mapea el PaymentOutcome a la respuesta.
 *
 * `fromSession` es la única fuente del contrato de sesión del flujo de
 * pago web y es 100% escalar (spec-session-serialization-json-fase5,
 * Fase 5 / 5.21): la sesión corre bajo `serialization=json`, los
 * modelos se rehidratan por ID en PaymentService::success y cualquier
 * valor de sesión no escalar se trata como ausente.
 */
final class PaymentState
{
    /**
     * @param  array<string, mixed>|null  $paymentInfo  confirmación del gateway (session('payment_info'))
     */
    public function __construct(
        public readonly int|string|null $paymentType,
        public readonly int|string|null $planId,
        public readonly int|string|null $userId,
        public readonly mixed $amount,
        public readonly int|string|null $gatewayId,
        public readonly ?array $paymentInfo,
        public readonly string $sessionId,
        public readonly ?string $stripeSessionId,
    ) {}

    /**
     * @param  string|null  $stripeSessionId  query session_id (verificación HTTP de Stripe).
     */
    public static function fromSession(?string $stripeSessionId = null): self
    {
        return new self(
            paymentType: self::sessionScalar('payment_type'),
            planId: self::sessionScalar('plan_id'),
            userId: self::sessionScalar('user_id'),
            amount: session('amount'),
            gatewayId: self::sessionScalar('gateway_id'),
            paymentInfo: self::sessionArray('payment_info'),
            sessionId: session()->getId(),
            stripeSessionId: $stripeSessionId,
        );
    }

    /**
     * Contrato escalar compartido (spec 5.21): la sesión guarda solo
     * scalares bajo serialization=json — cualquier otro tipo se trata
     * como ausente. Lo usan PaymentState y los writers de sesión.
     */
    public static function scalarValue(mixed $value): int|string|null
    {
        return is_int($value) || is_string($value) ? $value : null;
    }

    /**
     * Valor escalar de sesión (int|string): el flujo guarda valores
     * crudos (ids de URL como string, ids de modelo como int).
     */
    private static function sessionScalar(string $key): int|string|null
    {
        return self::scalarValue(session($key));
    }

    private static function sessionArray(string $key): ?array
    {
        $value = session($key);

        return is_array($value) ? $value : null;
    }
}
