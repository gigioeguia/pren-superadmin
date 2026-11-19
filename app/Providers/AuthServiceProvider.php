<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Application;
use App\Models\LandlordWithdraw;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\User;
use App\Models\Withdraw;
use App\Policies\ApplicationPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\RentPaymentPolicy;
use App\Policies\SecurityDepositPolicy;
use App\Policies\WithdrawPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Property::class => PropertyPolicy::class,
        Application::class => ApplicationPolicy::class,
        SecurityDeposit::class => SecurityDepositPolicy::class,
        Withdraw::class => WithdrawPolicy::class,
        LandlordWithdraw::class => WithdrawPolicy::class,
        RentPayment::class => RentPaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('isAdmin', function (User $user) {
            return $user->permissions()->first()->menu_slug;
        });

        //
    }
}
