<?php

use App\Models\User;
use App\Models\Option;
use App\Models\Gateway;
use App\Models\Currency;
use App\Models\Language;
use App\Models\PlanSubscribe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

function expectedItemId(): string
{
    return 59386452;
}

function getArrow(float|int $last, float|int $current): string
{
    return $current > $last ? 'up' : ($current < $last ? 'down' : 'same');
}

function cache_remember(string $key, callable $callback, int $ttl = 1800): mixed
{
    return cache()->remember($key, env('CACHE_LIFETIME', $ttl), $callback);
}

function get_option(string $key): mixed
{
    return cache_remember($key, fn() => Option::where('key', $key)->value('value') ?? []);
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
    $amount = number_format($amount, $decimals);
    $currency = $currency ?? default_currency();

    $symbolOrCode = ($type === 'icon' || $type === 'symbol') ? $currency->symbol : $currency->code;

    return $currency->position === 'right'
        ? "{$amount} {$symbolOrCode}"
        : "{$symbolOrCode} {$amount}";
}

function currency()
{
    $defaultCurrency = Currency::where('is_default', 1)->first();;

    if ($defaultCurrency) {
        return (object)[
            'name' => $defaultCurrency->name,
            'rate' => $defaultCurrency->rate,
            'code' => $defaultCurrency->code,
            'symbol' => $defaultCurrency->symbol,
            'position' => $defaultCurrency->position,
        ];
    }

    return default_currency();
}

function convert_money(float|int $amount, Currency $currency, bool $multiply = true): float|int
{
    if ($currency->code === default_currency('code')) {
        return $amount;
    }

    return $multiply ? $amount * $currency->rate : $amount / $currency->rate;
}

function payable(float|int $amount, Gateway $gateway): float|int
{
    if ($gateway->currency->code === default_currency('code')) {
        return $amount + $gateway->charge;
    }

    return (convert_to_default_amount($gateway->charge, $gateway->currency) * $gateway->currency->rate) + $gateway->charge;
}

function convert_to_default_amount(float|int $amount, Currency $currency): float|int
{
    return $amount * $currency->rate;
}

function default_currency(?string $key = null, ?Currency $currency = null): mixed
{
    $currency = $currency ?? cache_remember('default_currency', function () {
        return Currency::where('is_default', 1)->first() ?? (object)[
            'name'      => 'US Dollar',
            'code'      => 'USD',
            'rate'      => 1,
            'symbol'    => '$',
            'position'  => 'left',
            'status'    => true,
            'is_default' => true,
        ];
    });

    return $key ? $currency->$key : $currency;
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
    return cache_remember('plan-data-' . ($landlord_id ?? auth()->id()), function () use ($landlord_id) {
        $planSubscribe = PlanSubscribe::where('user_id', $landlord_id ?? auth()->id())->latest()->first();

        if ($planSubscribe) {
            $user = User::find($planSubscribe->user_id);
            $planSubscribe->will_expire = $user->will_expire ?? null;
        }

        return $planSubscribe;
    });
}

function languages()
{
    return cache_remember('languages', function () {
        return Language::orderBy('order', 'asc')->where('status', 1)->get();
    });
}

function demoReset()
{
    if (!env('DEMO_MODE')) {
        return true;
    }

    DB::table('properties')->where('landlord_id', 3)->delete();
    DB::table('expense_categories')->where('landlord_id', 3)->delete();
    DB::table('income_categories')->where('landlord_id', 3)->delete();
    DB::table('expenses')->where('landlord_id', 3)->delete();
    DB::table('incomes')->where('landlord_id', 3)->delete();

    Artisan::call('db:seed', ['--class' => 'DemoSeeder']);
}
