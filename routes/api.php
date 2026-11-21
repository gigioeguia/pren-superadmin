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
            Route::apiResource('facilities', Api\AcnooFacilitiesController::class)->except('create', 'edit', 'show');
            Route::apiResource('amenities', Api\AcnooAmenitiesController::class)->except('create', 'edit', 'show');
            Route::apiResource('properties', Api\AcnooPropertyController::class);
            Route::post('properties/status', [Api\AcnooPropertyController::class, 'update_status'])->name('properties.status');
            Route::apiResource('tenant', Api\AcnooTenantController::class);
            Route::post('application/processing/{id}', [Api\AcnooLandlordApplicationController::class, 'processing'])->name('application.processing');
            Route::post('application/approved/{id}', [Api\AcnooLandlordApplicationController::class, 'approved'])->name('application.approved');
            Route::post('application/rejected/{id}', [Api\AcnooLandlordApplicationController::class, 'rejected'])->name('application.rejected');
            Route::get('application/file/{id}', [Api\AcnooLandlordApplicationController::class, 'file_download'])->name('application.file');
            Route::apiResource('rent', Api\AcnooRentController::class);
            Route::post('rent/approved/{id}', [Api\AcnooRentController::class, 'approved'])->name('rent.approved');
            Route::post('rent/accept/{id}', [Api\AcnooRentController::class, 'tenant_accept'])->name('rent.accept');
            Route::post('rent/cancelled/{id}', [Api\AcnooRentController::class, 'tenant_cancelled'])->name('rent.cancelled');
            Route::post('rent/agreement-sign/{id}', [Api\AcnooRentController::class, 'tenant_agreement'])->name('rent.agreement-sign');
            Route::apiResource('labors', Api\AcnooLaborController::class);
            Route::apiResource('maintenance', Api\AcnooLandlordMaintenanceController::class)->only('index')->names(['index' => 'landlord-maintenance.index']);
            Route::post('maintenance/rejected/{id}', [Api\AcnooLandlordMaintenanceController::class, 'rejected'])->name('landlord-maintenance.rejected');
            Route::post('maintenance/processing/{id}', [Api\AcnooLandlordMaintenanceController::class, 'processing'])->name('landlord-maintenance.processing');
            Route::post('maintenance/completed/{id}', [Api\AcnooLandlordMaintenanceController::class, 'completed'])->name('landlord-maintenance.completed');
            Route::get('maintenance/reports', [Api\AcnooLandlordMaintenanceController::class, 'reports'])->name('landlord-maintenance.reports');
            Route::apiResource('withdraw-methods', Api\AcnooWithdrawMethodController::class)->except('show');
            Route::get('user-withdraw-methods', [Api\AcnooWithdrawMethodController::class, 'user_withdraw_method'])->name('user-withdraw-methods');
            Route::apiResource('withdraw', Api\AcnooWithdrawController::class)->only('index', 'store');
            Route::get('withdraw/invoice/{id}', [Api\AcnooWithdrawController::class, 'invoice'])->name('withdraw.invoice');
            Route::get('withdraw/summary/{id}', [Api\AcnooWithdrawController::class, 'summary'])->name('withdraw.summary');
            Route::apiResource('dashboard', Api\AcnooLandlordDashboardController::class)->only('index');
            Route::get('dashboard/property-chart', [Api\AcnooLandlordDashboardController::class, 'property_chart'])->name('dashboard.property-chart');
            Route::get('dashboard/account-chart', [Api\AcnooLandlordDashboardController::class, 'account_chart'])->name('dashboard.account-chart');
            Route::apiResource('supports', Api\AcnooSupportController::class);

            Route::apiResource('expense-categories', Api\AcnooExpenseCategoryController::class)->except('show');
            Route::apiResource('expenses', Api\AcnooExpenseController::class)->except('show');

            Route::apiResource('income-categories', Api\AcnooIncomeCategoryController::class)->except('show');
            Route::apiResource('incomes', Api\AcnooIncomeController::class)->except('show');
        });

        Route::prefix('tenant')->group(function () {
            Route::apiResource('application', Api\AcnooTenantApplicationController::class)->only('index', 'store', 'show');
            Route::post('application/sign/{id}', [Api\AcnooTenantApplicationController::class, 'agreement_sign'])->name('tenant-application.sign');
            Route::apiResource('security-deposits', Api\AcnooSecurityDepositController::class)->only('index', 'show');
            Route::post('offline-payment/{gateway_id}/{deposit_id}', [Api\AcnooSecurityDepositController::class, 'offline_payment'])->name('tenant-offline-payment');
            Route::apiResource('review', Api\AcnooReviewController::class)->only('store', 'update');
            Route::apiResource('favorite', Api\AcnooFavoriteController::class)->only('index', 'store', 'destroy');
            Route::apiResource('maintenance', Api\AcnooTenantMaintenanceController::class)->only('index', 'store', 'show', 'update')->names(['index' => 'tenant-maintenance.index']);
            Route::apiResource('refund-reason', Api\AcnooRefundReasonController::class)->only('index');
            Route::apiResource('refund-request', Api\AcnooRefundRequestController::class)->only('index', 'store', 'show');
            Route::post('refund-request/upload-receipt/{id}', [Api\AcnooRefundRequestController::class, 'upload_receipt'])->name('refund-request.upload-receipt');
            Route::post('refund-request/approved/{id}', [Api\AcnooRefundRequestController::class, 'approved'])->name('refund-request.approved');
            Route::apiResource('rent-payments', Api\AcnooRentPaymentController::class)->only('index', 'show');
        });

        Route::apiResource('plans', Api\AcnooSubscriptionsController::class)->only('index');
        Route::apiResource('subscribes', Api\AcnooSubscribesController::class)->only('index');

        Route::apiResource('house-types', Api\AcnooHouseTypeController::class)->only('index');
        Route::apiResource('term-condition', Api\AcnooTermConditionController::class)->only('index');
        Route::apiResource('about-us', Api\AcnooAboutUsController::class)->only('index');
        Route::apiResource('privacy-policy', Api\AcnooPrivacyPolicyController::class)->only('index');
        Route::apiResource('get-notifications', Api\AcnooNotificationController::class)->only('index');
        Route::get('read-notifications', [Api\AcnooNotificationController::class, 'readAll'])->name('read-notifications');

        Route::get('/sign-out', [Auth\SigninController::class, 'maanSignOut'])->name('sign-out');
        Route::post('/refresh-token', [Auth\SigninController::class, 'userRefreshToken'])->name('refresh-token');
        Route::get('/payment-url', [PaymentController::class, 'paymentUrl'])->name('payment-url');
    });
});
