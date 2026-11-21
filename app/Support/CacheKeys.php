<?php

namespace App\Support;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Option;
use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Dueño único de las claves de caché y su invalidación (spec 4.9, DT-M8).
 *
 * Las strings son las históricas intactas (Q12): cambiarlas orfanaría el
 * store desplegado. Toda invalidación pasa por forgetModel() (Q4/Q11);
 * los observers y la excepción masiva de isDefault confluyen aquí.
 */
class CacheKeys
{
    public static function option(string $key): string
    {
        return $key;
    }

    /**
     * Clave por-landlord: el id es obligatorio (Q3) para que el scope
     * multi-tenant sea estructural, no disciplina del caller.
     *
     * La clave embebe la fila de PlanSubscribe y will_expire de User:
     * si plan_data() empieza a embeber de otro modelo, añadir la rama
     * correspondiente en forgetModel().
     */
    public static function planData(int $userId): string
    {
        return 'plan-data-'.$userId;
    }

    public static function languages(): string
    {
        return 'languages';
    }

    public static function defaultCurrency(): string
    {
        return 'default_currency';
    }

    /**
     * Invalida las claves derivadas del modelo (Q6/Q11). Único punto de
     * invalidación del app: observers (saved/deleted) y mass-ops (Q15a).
     */
    public static function forgetModel(Model $model): void
    {
        $keys = match (true) {
            $model instanceof Option => [self::option((string) $model->key)],
            $model instanceof Language => [self::languages()],
            $model instanceof Currency => [self::defaultCurrency()],
            $model instanceof PlanSubscribe => [self::planData((int) $model->user_id)],
            $model instanceof User => [self::planData((int) $model->id)],
            default => [],
        };

        foreach ($keys as $key) {
            cache()->forget($key);
        }
    }
}
