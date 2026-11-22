<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gateway extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'mode',
        'data',
        'image',
        'status',
        'charge',
        'is_manual',
        'namespace',
        'min_amount',
        'max_amount',
        'accept_img',
        'manual_data',
        'currency_id',
        'instructions',
        'phone_required',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * FQCN list allowed to be invoked dynamically from `gateways.namespace`.
     *
     * In production this is the config list. In the test environment the
     * `Tests\Stubs\StubGateway` class is auto-included when available
     * (autoload-dev) so payment tests keep working without production coupling.
     *
     * @return array<int, string>
     */
    public static function allowedNamespaces(): array
    {
        $namespaces = (array) config('gateways.allowed_namespaces', []);

        // Auto-include the test stub only when it actually exists.
        if (class_exists('Tests\\Stubs\\StubGateway')) {
            $namespaces[] = 'Tests\\Stubs\\StubGateway';
        }

        return array_values(array_unique($namespaces));
    }

    /**
     * Whether the given namespace is allowed to be invoked dynamically.
     */
    public static function isAllowedNamespace(?string $namespace): bool
    {
        if ($namespace === null || $namespace === '' || ! class_exists($namespace)) {
            return false;
        }

        return in_array($namespace, self::allowedNamespaces(), true);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
        'manual_data' => 'json',
    ];
}
