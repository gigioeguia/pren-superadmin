<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::controller(Api\UserController::class)->group(function () {
        // facility all ..
        Route::get('/facility', 'facilities');
        // amenity all ..
        Route::get('/amenity', 'amenities');
    });

    Route::post('/sign-up', [Auth\RegistrationController::class, 'maanRegistration'])->name('sign-up');
    Route::post('/submit-otp', [Auth\RegistrationController::class, 'submitOtp'])->name('submit-otp');
    Route::post('sign-in', [Auth\SigninController::class, 'maanSignIn'])->name('sign-in');

    Route::post('/send-reset-code', [Auth\ForgotPasswordController::class, 'sendResetCode']);
    Route::post('/resend-otp', [Auth\ForgotPasswordController::class, 'resendOtp']);
    Route::post('/user-password-reset', [Auth\ResetPasswordController::class, 'userResetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(Api\UserController::class)->group(function () {
            Route::get('/user', 'userInfo');
            Route::post('/profile-setup', 'profileSetUp');
            Route::get('/offline-gateway', 'offline_gateway');
        });

        Route::prefix('landlord')->group(function () {
            Route::apiResource('facilities', Api\AcnooFacilitiesController::class)->except('create', 'edit', 'show');
            Route::apiResource('amenities', Api\AcnooAmenitiesController::class)->except('create', 'edit', 'show');
            Route::apiResource('properties', Api\AcnooPropertyController::class);
            Route::post('properties/status', [Api\AcnooPropertyController::class, 'update_status']);
            Route::apiResource('tenant', Api\AcnooTenantController::class);
            Route::post('application/processing/{id}', [Api\AcnooLandlordApplicationController::class, 'processing']);
            Route::post('application/approved/{id}', [Api\AcnooLandlordApplicationController::class, 'approved']);
            Route::post('application/rejected/{id}', [Api\AcnooLandlordApplicationController::class, 'rejected']);
            Route::get('application/file/{id}', [Api\AcnooLandlordApplicationController::class, 'file_download']);
            Route::apiResource('rent', Api\AcnooRentController::class);
            Route::post('rent/approved/{id}', [Api\AcnooRentController::class, 'approved']);
            Route::post('rent/accept/{id}', [Api\AcnooRentController::class, 'tenant_accept']);
            Route::post('rent/cancelled/{id}', [Api\AcnooRentController::class, 'tenant_cancelled']);
            Route::post('rent/agreement-sign/{id}', [Api\AcnooRentController::class, 'tenant_agreement']);
            Route::apiResource('labors', Api\AcnooLaborController::class);
            Route::apiResource('maintenance', Api\AcnooLandlordMaintenanceController::class)->only('index');
            Route::post('maintenance/rejected/{id}', [Api\AcnooLandlordMaintenanceController::class, 'rejected']);
            Route::post('maintenance/processing/{id}', [Api\AcnooLandlordMaintenanceController::class, 'processing']);
            Route::post('maintenance/completed/{id}', [Api\AcnooLandlordMaintenanceController::class, 'completed']);
            Route::get('maintenance/reports', [Api\AcnooLandlordMaintenanceController::class, 'reports']);
            Route::apiResource('withdraw-methods', Api\AcnooWithdrawMethodController::class)->except('show');
            Route::get('user-withdraw-methods', [Api\AcnooWithdrawMethodController::class, 'user_withdraw_method']);
            Route::apiResource('withdraw', Api\AcnooWithdrawController::class)->only('index', 'store');
            Route::get('withdraw/invoice/{id}', [Api\AcnooWithdrawController::class, 'invoice']);
            Route::get('withdraw/summary/{id}', [Api\AcnooWithdrawController::class, 'summary']);
            Route::apiResource('dashboard', Api\AcnooLandlordDashboardController::class)->only('index');
            Route::get('dashboard/property-chart', [Api\AcnooLandlordDashboardController::class, 'property_chart']);
            Route::get('dashboard/account-chart', [Api\AcnooLandlordDashboardController::class, 'account_chart']);
            Route::apiResource('supports', Api\AcnooSupportController::class);

            Route::apiResource('expense-categories', Api\AcnooExpenseCategoryController::class)->except('show');
            Route::apiResource('expenses', Api\AcnooExpenseController::class)->except('show');

            Route::apiResource('income-categories', Api\AcnooIncomeCategoryController::class)->except('show');
            Route::apiResource('incomes', Api\AcnooIncomeController::class)->except('show');
        });

        Route::prefix('tenant')->group(function () {
            Route::apiResource('application', Api\AcnooTenantApplicationController::class)->only('index', 'store', 'show');
            Route::post('application/sign/{id}', [Api\AcnooTenantApplicationController::class, 'agreement_sign']);
            Route::apiResource('security-deposits', Api\AcnooSecurityDepositController::class)->only('index', 'show');
            Route::post('offline-payment/{gateway_id}/{deposit_id}', [Api\AcnooSecurityDepositController::class, 'offline_payment']);
            Route::apiResource('review', Api\AcnooReviewController::class)->only('store', 'update');
            Route::apiResource('favorite', Api\AcnooFavoriteController::class)->only('index', 'store', 'destroy');
            Route::apiResource('maintenance', Api\AcnooTenantMaintenanceController::class)->only('index', 'store', 'show', 'update');
            Route::apiResource('refund-reason', Api\AcnooRefundReasonController::class)->only('index');
            Route::apiResource('refund-request', Api\AcnooRefundRequestController::class)->only('index', 'store', 'show');
            Route::post('refund-request/upload-receipt/{id}', [Api\AcnooRefundRequestController::class, 'upload_receipt']);
            Route::post('refund-request/approved/{id}', [Api\AcnooRefundRequestController::class, 'approved']);
            Route::apiResource('rent-payments', Api\AcnooRentPaymentController::class)->only('index', 'show');
        });

        Route::apiResource('plans', Api\AcnooSubscriptionsController::class)->only('index');
        Route::apiResource('subscribes', Api\AcnooSubscribesController::class)->only('index');

        Route::apiResource('house-types', Api\AcnooHouseTypeController::class)->only('index');
        Route::apiResource('term-condition', Api\AcnooTermConditionController::class)->only('index');
        Route::apiResource('about-us', Api\AcnooAboutUsController::class)->only('index');
        Route::apiResource('privacy-policy', Api\AcnooPrivacyPolicyController::class)->only('index');
        Route::apiResource('get-notifications', Api\AcnooNotificationController::class)->only('index');
        Route::get('read-nitifications', [Api\AcnooNotificationController::class, 'readAll']);

        Route::get('/sign-out', [Auth\SigninController::class, 'maanSignOut']);
        Route::post('/refresh-token', [Auth\SigninController::class, 'userRefreshToken']);
    });
});
