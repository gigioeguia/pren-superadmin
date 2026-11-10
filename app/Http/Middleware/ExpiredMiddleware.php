<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Labor;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ExpiredMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the plan has expired
        $plan_subscribe = plan_data();

        if (!$plan_subscribe || !$plan_subscribe->will_expire || $plan_subscribe->will_expire < now()) {
            $message = __('Your plan has expired. Please subscribe to a new plan. You can only view data when your plan has expired.');

            $disabledRoutes = [
                'landlord.dashboard',
                'landlord.profile.update',
                'landlord.properties.index',
                'landlord.properties.create',
                'landlord.properties.store',
                'landlord.properties.update',
                'landlord.properties.destroy',
                'landlord.properties.delete-all',
                'landlord.tenants.index',
                'landlord.tenants.create',
                'landlord.tenants.store',
                'landlord.tenants.update',
                'landlord.tenants.destroy',
                'landlord.tenants.delete-all',
                'landlord.tenant-applications.index',
                'landlord.tenant-applications.show',
                'landlord.maintenances.index',
                'landlord.maintenances.show',
                'landlord.maintenance-reports.index',
                'landlord.labors.index',
                'landlord.labors.create',
                'landlord.labors.store',
                'landlord.labors.update',
                'landlord.labors.destroy',
                'landlord.labors.delete-all',
                'landlord.rents.index',
                'landlord.rents.store',
                'landlord.rents.show',
                'landlord.income-categories.index',
                'landlord.income-categories.create',
                'landlord.income-categories.store',
                'landlord.income-categories.update',
                'landlord.income-categories.destroy',
                'landlord.income-categories.delete-all',
                'landlord.incomes.index',
                'landlord.incomes.create',
                'landlord.incomes.store',
                'landlord.incomes.update',
                'landlord.incomes.destroy',
                'landlord.incomes.delete-all',
                'landlord.expense-categories.index',
                'landlord.expense-categories.create',
                'landlord.expense-categories.store',
                'landlord.expense-categories.update',
                'landlord.expense-categories.destroy',
                'landlord.expense-categories.delete-all',
                'landlord.expenses.index',
                'landlord.expenses.create',
                'landlord.expenses.store',
                'landlord.expenses.update',
                'landlord.expenses.destroy',
                'landlord.expenses.delete-all',
                'landlord.rent-payments.index',
                'landlord.rent-payments.show',
                'landlord.security-deposits.index',
                'landlord.security-deposits.show',
                'landlord.refund-request.index',
                'landlord.refund-request.show',
                'landlord.withdraw-method.index',
                'landlord.withdraw-method.store',
                'landlord.withdraw-method.destroy',
                'landlord.withdraw-method.delete-all',
                'landlord.withdraw.index',
                'landlord.supports.index',
                'landlord.notifications.index',
            ];

            if ($request->isMethod('delete')) {
                return response()->json($message, 406);
            }

            if (in_array(Route::currentRouteName(), $disabledRoutes)) {
                return $request->wantsJson()
                    ? response()->json($message, 406)
                    : redirect(route('landlord.subscriptions.index'))->with('error', $message);
            }
        }

        $user_id = auth()->id();
        $plan = $plan_subscribe->plan;
        // Check if the property limit is exceeded
        if ($request->isMethod('post') && $request->routeIs('landlord.properties.store')) {
            $property_count = Property::where('landlord_id', $user_id)->count();
            if ($plan->number_of_property !== -1 && $property_count >= $plan->number_of_property) {
                return $request->wantsJson()
                    ? response()->json(__('Your property addition limit has been reached. Your current limit is ' . $plan->number_of_property), 406)
                    : redirect(route('landlord.subscriptions.index'))->with('error', __('Property limit exceeded. Your current limit is ' . $plan->number_of_property));
            }
        }

        // Check if the labor limit is exceeded
        if ($request->isMethod('post') && $request->routeIs('landlord.labors.store')) {
            $labor_count = Labor::where('landlord_id', $user_id)->count();
            if ($plan->number_of_labor !== -1 && $labor_count >= $plan->number_of_labor) {
                return $request->wantsJson()
                    ? response()->json(__('Your labor addition limit has been reached. Your current limit is ' . $plan->number_of_labor), 406)
                    : redirect(route('landlord.subscriptions.index'))->with('error', __('Labor limit exceeded. Your current limit is ' . $plan->number_of_labor));
            }
        }

        // Check if the tenant limit is exceeded
        if ($request->isMethod('post') && $request->routeIs('landlord.tenants.store')) {
            $tenant_count = User::where([['landlord_id', $user_id], ['role', 'tenant']])->count();
            if ($plan->number_of_tenant !== -1 && $tenant_count >= $plan->number_of_tenant) {
                return $request->wantsJson()
                    ? response()->json(__('Your tenant addition limit has been reached. Your current limit is ' . $plan->number_of_tenant), 406)
                    : redirect(route('landlord.tenants.index'))->with('error', __('Tenant limit exceeded. Your current limit is ' . $plan->number_of_tenant));
            }
        }

        return $next($request);
    }
}
