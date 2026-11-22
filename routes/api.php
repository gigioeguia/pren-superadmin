<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\Auth;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::controller(Api\UserController::class)->group(function () {
        // facility all ..
        Route::get('/facility', 'facilities')->name('facilities.all');
        // amenity all ..
        Route::get('/amenity', 'amenities')->name('amenities.all');
    });

    Route::post('/sign-up', [Auth\RegistrationController::class, 'maanRegistration'])->name('sign-up')->middleware('throttle:5,1');
    Route::post('/submit-otp', [Auth\RegistrationController::class, 'submitOtp'])->name('submit-otp')->middleware('throttle:5,1');
    Route::post('sign-in', [Auth\SigninController::class, 'maanSignIn'])->name('sign-in')->middleware('throttle:5,1');

    Route::post('/send-reset-code', [Auth\ForgotPasswordController::class, 'sendResetCode'])->name('send-reset-code')->middleware('throttle:5,1');
    Route::post('/resend-otp', [Auth\ForgotPasswordController::class, 'resendOtp'])->name('resend-otp')->middleware('throttle:5,1');
    Route::post('/user-password-reset', [Auth\ResetPasswordController::class, 'userResetPassword'])->name('user-password-reset')->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(Api\UserController::class)->group(function () {
            Route::get('/user', 'userInfo')->name('user-info');
            Route::post('/profile-setup', 'profileSetUp')->name('profile-setup');
            Route::get('/offline-gateway', 'offline_gateway')->name('offline-gateway');
        });

        Route::prefix('landlord')->group(function () {
            Route::apiResource('facilities', Api\FacilitiesController::class)->except('create', 'edit', 'show');
            Route::apiResource('amenities', Api\AmenitiesController::class)->except('create', 'edit', 'show');
            Route::apiResource('properties', Api\PropertyController::class);
            Route::post('properties/status', [Api\PropertyController::class, 'update_status'])->name('properties.status');
            Route::apiResource('tenant', Api\TenantController::class);
            Route::post('application/processing/{id}', [Api\LandlordApplicationController::class, 'processing'])->name('application.processing');
            Route::post('application/approved/{id}', [Api\LandlordApplicationController::class, 'approved'])->name('application.approved');
            Route::post('application/rejected/{id}', [Api\LandlordApplicationController::class, 'rejected'])->name('application.rejected');
            Route::get('application/file/{id}', [Api\LandlordApplicationController::class, 'file_download'])->name('application.file');
            Route::apiResource('rent', Api\RentController::class)->only(['index', 'store', 'show']);
            Route::post('rent/approved/{id}', [Api\RentController::class, 'approved'])->name('rent.approved');
            Route::post('rent/accept/{id}', [Api\RentController::class, 'tenant_accept'])->name('rent.accept');
            Route::post('rent/cancelled/{id}', [Api\RentController::class, 'tenant_cancelled'])->name('rent.cancelled');
            Route::post('rent/agreement-sign/{id}', [Api\RentController::class, 'tenant_agreement'])->name('rent.agreement-sign');
            Route::apiResource('labors', Api\LaborController::class);
            Route::apiResource('maintenance', Api\LandlordMaintenanceController::class)->only('index')->names(['index' => 'landlord-maintenance.index']);
            Route::post('maintenance/rejected/{id}', [Api\LandlordMaintenanceController::class, 'rejected'])->name('landlord-maintenance.rejected');
            Route::post('maintenance/processing/{id}', [Api\LandlordMaintenanceController::class, 'processing'])->name('landlord-maintenance.processing');
            Route::post('maintenance/completed/{id}', [Api\LandlordMaintenanceController::class, 'completed'])->name('landlord-maintenance.completed');
            Route::get('maintenance/reports', [Api\LandlordMaintenanceController::class, 'reports'])->name('landlord-maintenance.reports');
            Route::apiResource('withdraw-methods', Api\WithdrawMethodController::class)->except('show');
            Route::get('user-withdraw-methods', [Api\WithdrawMethodController::class, 'user_withdraw_method'])->name('user-withdraw-methods');
            Route::apiResource('withdraw', Api\WithdrawController::class)->only('index', 'store');
            Route::get('withdraw/invoice/{id}', [Api\WithdrawController::class, 'invoice'])->name('withdraw.invoice');
            Route::get('withdraw/summary/{id}', [Api\WithdrawController::class, 'summary'])->name('withdraw.summary');
            Route::apiResource('dashboard', Api\LandlordDashboardController::class)->only('index');
            Route::get('dashboard/property-chart', [Api\LandlordDashboardController::class, 'property_chart'])->name('dashboard.property-chart');
            Route::get('dashboard/account-chart', [Api\LandlordDashboardController::class, 'account_chart'])->name('dashboard.account-chart');
            Route::apiResource('supports', Api\SupportController::class);

            Route::apiResource('expense-categories', Api\ExpenseCategoryController::class)->except('show');
            Route::apiResource('expenses', Api\ExpenseController::class)->except('show');

            Route::apiResource('income-categories', Api\IncomeCategoryController::class)->except('show');
            Route::apiResource('incomes', Api\IncomeController::class)->except('show');
        });

        Route::prefix('tenant')->group(function () {
            Route::apiResource('application', Api\TenantApplicationController::class)->only('index', 'store', 'show');
            Route::post('application/sign/{id}', [Api\TenantApplicationController::class, 'agreement_sign'])->name('tenant-application.sign');
            Route::apiResource('security-deposits', Api\SecurityDepositController::class)->only('index', 'show');
            Route::post('offline-payment/{gateway_id}/{deposit_id}', [Api\SecurityDepositController::class, 'offline_payment'])->name('tenant-offline-payment');
            Route::apiResource('review', Api\ReviewController::class)->only('store', 'update');
            Route::apiResource('favorite', Api\FavoriteController::class)->only('index', 'store', 'destroy');
            Route::apiResource('maintenance', Api\TenantMaintenanceController::class)->only('index', 'store', 'show', 'update')->names(['index' => 'tenant-maintenance.index']);
            Route::apiResource('refund-reason', Api\RefundReasonController::class)->only('index');
            Route::apiResource('refund-request', Api\RefundRequestController::class)->only('index', 'store', 'show');
            Route::post('refund-request/upload-receipt/{id}', [Api\RefundRequestController::class, 'upload_receipt'])->name('refund-request.upload-receipt');
            Route::post('refund-request/approved/{id}', [Api\RefundRequestController::class, 'approved'])->name('refund-request.approved');
            Route::apiResource('rent-payments', Api\RentPaymentController::class)->only('index', 'show');
        });

        Route::apiResource('plans', Api\SubscriptionsController::class)->only('index');
        Route::apiResource('subscribes', Api\SubscribesController::class)->only('index');

        Route::apiResource('house-types', Api\HouseTypeController::class)->only('index');
        Route::apiResource('term-condition', Api\TermConditionController::class)->only('index');
        Route::apiResource('about-us', Api\AboutUsController::class)->only('index');
        Route::apiResource('privacy-policy', Api\PrivacyPolicyController::class)->only('index');
        Route::apiResource('get-notifications', Api\NotificationController::class)->only('index');
        Route::get('read-notifications', [Api\NotificationController::class, 'readAll'])->name('read-notifications');

        Route::get('/sign-out', [Auth\SigninController::class, 'maanSignOut'])->name('sign-out');
        Route::post('/refresh-token', [Auth\SigninController::class, 'userRefreshToken'])->name('refresh-token');
        Route::get('/payment-url', [PaymentController::class, 'paymentUrl'])->name('payment-url');
    });
});
