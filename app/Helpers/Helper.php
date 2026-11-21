<?php

use App\Models\Currency;
use App\Models\Gateway;
use App\Models\Language;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Notifications\SendNotification;
use App\Support\CacheKeys;
use App\Support\Options;
use App\Support\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

function expectedItemId(): string
{
    return 59386452;
}

function getArrow(float|int $last, float|int $current): string
{
    return $current > $last ? 'up' : ($current < $last ? 'down' : 'same');
}

function cache_remember(string $key, callable $callback): mixed
{
    return cache()->remember($key, (int) config('cache.lifetime', 1800), $callback);
}

function forgetModel(Model $model): void
{
    CacheKeys::forgetModel($model);
}

function get_option(string $key): array
{
    return Options::array($key);
}

function formatted_date(string $date, string $format = 'd M, Y'): ?string
{
    return $date ? Date::parse($date)->format($format) : null;
}

function formatted_time(string $time, string $format = 'h:i A'): ?string
{
    return $time ? Date::parse($time)->format($format) : null;
}

function currency_format(float|int $amount, string $type = 'icon', int $decimals = 2, ?Currency $currency = null): string
{
    return App\Support\Currency::format($amount, $type, $decimals, $currency);
}

function currency()
{
    return App\Support\Currency::currency();
}

function convert_money(float|int $amount, Currency $currency, bool $multiply = true): float|int
{
    return App\Support\Currency::convert($amount, $currency, $multiply);
}

function payable(float|int $amount, Gateway $gateway): float|int
{
    return Payment::payable($amount, $gateway);
}

function convert_to_default_amount(float|int $amount, Currency $currency): float|int
{
    return App\Support\Currency::convertToDefault($amount, $currency);
}

function default_currency(?string $key = null, ?Currency $currency = null): mixed
{
    return App\Support\Currency::default($key, $currency);
}

function notify_users(array $users = []): Collection
{
    return User::whereIn('role', ['superadmin', 'admin'])
        ->orWhereIn('id', $users)
        ->get();
}

function sendNotification(
    $id,
    $users,
    ?string $admin_url = null,
    ?string $landlord_url = null,
    ?string $admin_msg = null,
    ?string $landlord_msg = null,
    ?string $tenant_msg = null
): void {
    $notify = [
        'id' => $id,
        'admin_url' => $admin_url ?? '',
        'landlord_url' => $landlord_url ?? '',
        'admin_msg' => $admin_msg,
        'landlord_msg' => $landlord_msg,
        'tenant_msg' => $tenant_msg,
    ];

    Notification::send($users, new SendNotification($notify));
}

function plan_data(?string $landlord_id = null): ?PlanSubscribe
{
    $userId = (int) ($landlord_id ?? auth()->id());

    return cache_remember(CacheKeys::planData($userId), function () use ($userId) {
        $planSubscribe = PlanSubscribe::where('user_id', $userId)->latest()->first();

        if ($planSubscribe) {
            $user = User::find($planSubscribe->user_id);
            $planSubscribe->will_expire = $user->will_expire ?? null;
        }

        return $planSubscribe;
    });
}

function languages()
{
    return cache_remember(CacheKeys::languages(), function () {
        return Language::orderBy('order', 'asc')->where('status', 1)->get();
    });
}

function demoReset(): bool
{
    if (! app()->environment('demo')) {
        Log::warning('demoReset skipped: not demo environment.');

        return false;
    }

    // El landlord demo se resuelve por is_demo, nunca por id fijo (spec 4.10 / D4).
    $demoLandlordIds = User::demoLandlords()->pluck('id');

    if ($demoLandlordIds->isNotEmpty()) {
        DB::table('properties')->whereIn('landlord_id', $demoLandlordIds)->delete();
        DB::table('expense_categories')->whereIn('landlord_id', $demoLandlordIds)->delete();
        DB::table('income_categories')->whereIn('landlord_id', $demoLandlordIds)->delete();
        DB::table('expenses')->whereIn('landlord_id', $demoLandlordIds)->delete();
        DB::table('incomes')->whereIn('landlord_id', $demoLandlordIds)->delete();
    }

    Artisan::call('db:seed', ['--class' => 'DemoSeeder']);

    return true;
}
