<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;

Route::get('/', [Admin\AcnooAdminDashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard-data', [Admin\AcnooAdminDashboardController::class, 'getDashboardData'])->name('getDashboardData');
Route::get('/property-chart', [Admin\AcnooAdminDashboardController::class, 'property_chart'])->name('getPropertyData');
Route::get('/account-data', [Admin\AcnooAdminDashboardController::class, 'account_chart'])->name('getAccountData');

Route::resource('profile', Admin\AcnooProfileController::class)->only('index', 'update');

Route::resource('users', ADMIN\UserController::class);
Route::post('users/filter', [ADMIN\UserController::class, 'acnooFilter'])->name('users.filter');
Route::post('users/status/{id}', [ADMIN\UserController::class, 'status'])->name('users.status');
Route::post('users/delete-all', [ADMIN\UserController::class, 'deleteAll'])->name('users.delete-all');

Route::resource('properties', Admin\PropertyController::class);
Route::post('properties/status/{id}', [Admin\PropertyController::class, 'status'])->name('properties.status');
Route::get('properties/success/{id}', [Admin\PropertyController::class, 'propertySuccess'])->name('properties.success');
Route::post('properties/filter', [ADMIN\PropertyController::class, 'acnooFilter'])->name('properties.filter');
Route::post('properties/delete-all', [ADMIN\PropertyController::class, 'deleteAll'])->name('properties.delete-all');
Route::post('properties/reject/{id}', [ADMIN\PropertyController::class, 'reject'])->name('properties.reject');
Route::post('properties/approve/{id}', [ADMIN\PropertyController::class, 'approve'])->name('properties.approve');
Route::get('properties-excel', [ADMIN\PropertyController::class, 'exportExcel'])->name('properties.excel');
Route::get('properties-csv', [ADMIN\PropertyController::class, 'exportCsv'])->name('properties.csv');
Route::get('properties/get-facilities/{landlordId}', [Admin\PropertyController::class, 'getFacilities']);
Route::get('properties/get-amenities/{landlordId}', [Admin\PropertyController::class, 'getAmenities']);

Route::resource('landlords', Admin\AcnooLandlordController::class);
Route::post('landlords/filter', [ADMIN\AcnooLandlordController::class, 'acnooFilter'])->name('landlords.filter');
Route::post('landlords/delete-all', [ADMIN\AcnooLandlordController::class, 'deleteAll'])->name('landlords.delete-all');
Route::get('landlords-excel', [ADMIN\AcnooLandlordController::class, 'exportExcel'])->name('landlords.excel');
Route::get('landlords-csv', [ADMIN\AcnooLandlordController::class, 'exportCsv'])->name('landlords.csv');

Route::resource('tenants', Admin\AcnooTenantController::class);
Route::post('tenants/filter', [ADMIN\AcnooTenantController::class, 'acnooFilter'])->name('tenants.filter');
Route::post('tenants/delete-all', [ADMIN\AcnooTenantController::class, 'deleteAll'])->name('tenants.delete-all');
Route::post('tenants/otp-submit', [ADMIN\AcnooTenantController::class, 'submitOtp'])->name('tenants.otp-submit');
Route::post('tenants/otp-resend', [ADMIN\AcnooTenantController::class, 'otpResend'])->name('tenants.otp-resend');
Route::get('tenant/excel', [ADMIN\AcnooTenantController::class, 'exportExcel'])->name('tenants.excel');
Route::get('tenant/csv', [ADMIN\AcnooTenantController::class, 'exportCsv'])->name('tenants.csv');
Route::get('tenant/file-download/{id}', [ADMIN\AcnooTenantController::class, 'file_download'])->name('tenants.file-download');

Route::resource('transactions', Admin\AcnooTransactionController::class);
Route::post('transactions/filter', [ADMIN\AcnooTransactionController::class, 'acnooFilter'])->name('transactions.filter');
Route::get('transactions-excel', [ADMIN\AcnooTransactionController::class, 'exportExcel'])->name('transactions.excel');
Route::get('transactions-csv', [ADMIN\AcnooTransactionController::class, 'exportCsv'])->name('transactions.csv');

Route::resource('gateways', Admin\AcnooGatewayController::class)->only('index', 'update');

// Route::resource('banner', Admin\BannerController::class)->only(['index', 'create', 'update']);
Route::resource('service-charges', ADMIN\ServiceChargeController::class)->only('index', 'update');

Route::resource('refund-reason', Admin\AcnooRefundReasonController::class);
Route::post('refund-reason/filter', [ADMIN\AcnooRefundReasonController::class, 'acnooFilter'])->name('refund-reason.filter');
Route::post('refund-reason/delete-all', [ADMIN\AcnooRefundReasonController::class, 'deleteAll'])->name('refund-reason.delete-all');
Route::post('refund-reason/status/{id}', [Admin\AcnooRefundReasonController::class, 'status'])->name('refund-reason.status');

Route::resource('plans', ADMIN\PlanController::class)->except('show');

// House Type
Route::resource('house-types', Admin\AcnooHouseTypeController::class);
Route::post('house-types/filter', [ADMIN\AcnooHouseTypeController::class, 'acnooFilter'])->name('house-types.filter');
Route::post('house-types/delete-all', [ADMIN\AcnooHouseTypeController::class, 'deleteAll'])->name('house-types.delete-all');
Route::post('house-types/status/{id}', [Admin\AcnooHouseTypeController::class, 'status'])->name('house-types.status');

// Facility
Route::resource('facilities', Admin\AcnooFacilityController::class);
Route::post('facilities/filter', [ADMIN\AcnooFacilityController::class, 'acnooFilter'])->name('facilities.filter');
Route::post('facilities/delete-all', [ADMIN\AcnooFacilityController::class, 'deleteAll'])->name('facilities.delete-all');
Route::post('facilities/status/{id}', [Admin\AcnooFacilityController::class, 'status'])->name('facilities.status');

// Amenity
Route::resource('amenities', Admin\AcnooAmenityController::class);
Route::post('amenities/filter', [ADMIN\AcnooAmenityController::class, 'acnooFilter'])->name('amenities.filter');
Route::post('amenities/delete-all', [ADMIN\AcnooAmenityController::class, 'deleteAll'])->name('amenities.delete-all');
Route::post('amenities/status/{id}', [Admin\AcnooAmenityController::class, 'status'])->name('amenities.status');

//Withdrawal Method
Route::resource('withdraw-methods', ADMIN\AcnooWithdrawMethodController::class);
Route::post('withdraw-methods/status/{id}', [ADMIN\AcnooWithdrawMethodController::class, 'status'])->name('withdraw-methods.status');
Route::post('withdraw-methods/filter', [ADMIN\AcnooWithdrawMethodController::class, 'acnooFilter'])->name('withdraw-methods.filter');
Route::post('withdraw-methods/delete-all', [ADMIN\AcnooWithdrawMethodController::class, 'deleteAll'])->name('withdraw-methods.delete-all');
Route::get('withdraw-methods-excel', [ADMIN\AcnooWithdrawMethodController::class, 'exportExcel'])->name('withdraw-methods.excel');
Route::get('withdraw-methods-csv', [ADMIN\AcnooWithdrawMethodController::class, 'exportCsv'])->name('withdraw-methods.csv');

//Withdrawal
Route::resource('withdraw-request', ADMIN\AcnooWithdrawRequestController::class)->only('index', 'show', 'store', 'reject');
Route::post('withdraw-request/filter', [ADMIN\AcnooWithdrawRequestController::class, 'acnooFilter'])->name('withdraw-request.filter');
Route::post('withdraw-request/reject/{id}', [ADMIN\AcnooWithdrawRequestController::class, 'reject'])->name('withdraw.reject');
Route::post('withdraw-request/approve/{id}', [ADMIN\AcnooWithdrawRequestController::class, 'approve'])->name('withdraw.approve');
Route::get('withdraw-request-excel', [ADMIN\AcnooWithdrawRequestController::class, 'exportExcel'])->name('withdraw-request.excel');
Route::get('withdraw-request-csv', [ADMIN\AcnooWithdrawRequestController::class, 'exportCsv'])->name('withdraw-request.csv');

Route::resource('rent-payments', ADMIN\AcnooRentPaymentController::class)->only('index');
Route::post('rent-payments/filter', [ADMIN\AcnooRentPaymentController::class, 'acnooFilter'])->name('rent-payments.filter');
Route::post('rent-payments/approve/{id}', [ADMIN\AcnooRentPaymentController::class, 'approve'])->name('rent-payments.approve');
Route::post('rent-payments/rejected/{id}', [ADMIN\AcnooRentPaymentController::class, 'rejected'])->name('rent-payments.reject');
Route::get('rent-payments/file-download/{id}', [ADMIN\AcnooRentPaymentController::class, 'file_download'])->name('rent-payments.download');
Route::get('rent-payments-excel', [ADMIN\AcnooRentPaymentController::class, 'exportExcel'])->name('rent-payments.excel');
Route::get('rent-payments-csv', [ADMIN\AcnooRentPaymentController::class, 'exportCsv'])->name('rent-payments.csv');


Route::resource('security-deposits', ADMIN\AcnooSecurityDepositController::class)->only('index');
Route::post('security-deposits/filter', [ADMIN\AcnooSecurityDepositController::class, 'acnooFilter'])->name('security-deposits.filter');
Route::post('security-deposits/approve/{id}', [ADMIN\AcnooSecurityDepositController::class, 'approve'])->name('security-deposits.approve');
Route::post('security-deposits/rejected/{id}', [ADMIN\AcnooSecurityDepositController::class, 'rejected'])->name('security-deposits.reject');
Route::get('security-deposits/file-download/{id}', [ADMIN\AcnooSecurityDepositController::class, 'file_download'])->name('security-deposits.download');
Route::get('security-deposits-excel', [ADMIN\AcnooSecurityDepositController::class, 'exportExcel'])->name('security-deposits.excel');
Route::get('security-deposits-csv', [ADMIN\AcnooSecurityDepositController::class, 'exportCsv'])->name('security-deposits.csv');


Route::resource('rent-payment-generate', ADMIN\AcnooRentPaymentGenerateController::class);

//Reminder Method
Route::resource('reminder-messages', ADMIN\AcnooReminderMessageController::class)->except('show');

// Roles & Permissions
Route::resource('roles', ADMIN\RoleController::class)->except('show');
Route::resource('permissions', ADMIN\PermissionController::class)->only('index', 'store');

// General Setting
Route::resource('settings', ADMIN\SettingController::class)->only('index', 'update');

// System Setting
Route::resource('system-settings', ADMIN\SystemSettingController::class)->only('index', 'store');

// Currencies
Route::resource('currencies', ADMIN\AcnooCurrencyController::class)->except('show');
Route::post('currencies/filter', [ADMIN\AcnooCurrencyController::class, 'acnooFilter'])->name('currencies.filter');
Route::match(['get', 'post'], 'currencies/default/{id}', [ADMIN\AcnooCurrencyController::class, 'default'])->name('currencies.default');
Route::post('currencies/delete-all', [ADMIN\AcnooCurrencyController::class,'deleteAll'])->name('currencies.delete-all');

// Subscriptions
Route::resource('subscriptions', Admin\AcnooSubscriptionController::class)->only('index');
Route::post('subscriptions/filter', [ADMIN\AcnooSubscriptionController::class, 'acnooFilter'])->name('subscriptions.filter');
Route::post('subscriptions/reject/{id}', [ADMIN\AcnooSubscriptionController::class, 'reject'])->name('subscriptions.reject');
Route::post('subscriptions/paid/{id}', [ADMIN\AcnooSubscriptionController::class, 'paid'])->name('subscriptions.paid');
Route::get('subscriptions/get-invoice/{id}', [ADMIN\AcnooSubscriptionController::class, 'getInvoice'])->name('subscriptions.invoice');
Route::get('subscriptions-excel', [ADMIN\AcnooSubscriptionController::class, 'exportExcel'])->name('subscriptions.excel');
Route::get('subscriptions-csv', [ADMIN\AcnooSubscriptionController::class, 'exportCsv'])->name('subscriptions.csv');
Route::get('subscriptions/file-download/{id}', [ADMIN\AcnooSubscriptionController::class, 'file_download'])->name('subscriptions.download');

// Report
Route::resource('refund-request', Admin\AcnooRefundRequestController::class)->only('index', 'show');
Route::post('refund-request/filter', [ADMIN\AcnooRefundRequestController::class, 'acnooFilter'])->name('refund-request.filter');
Route::get('refund-request-reports-excel', [ADMIN\AcnooRefundRequestController::class, 'exportExcel'])->name('refund-request-reports.excel');
Route::get('refund-request-reports-csv', [ADMIN\AcnooRefundRequestController::class, 'exportCsv'])->name('refund-request-reports.csv');

Route::resource('landlord-withdraws', Admin\AcnooLandlordWithdrawReportController::class)->only('index', 'show');
Route::post('landlord-withdraws/filter', [ADMIN\AcnooLandlordWithdrawReportController::class, 'acnooFilter'])->name('landlord-withdraws.filter');
Route::get('landlord-withdraws-excel', [ADMIN\AcnooLandlordWithdrawReportController::class, 'exportExcel'])->name('landlord-withdraws.excel');
Route::get('landlord-withdraws-csv', [ADMIN\AcnooLandlordWithdrawReportController::class, 'exportCsv'])->name('landlord-withdraws.csv');


Route::resource('rent-payment-reports', Admin\AcnooRentPaymentReportController::class)->only('index', 'show');
Route::post('rent-payment-reports/filter', [ADMIN\AcnooRentPaymentReportController::class, 'acnooFilter'])->name('rent-payment-reports.filter');
Route::get('rent-payment-reports-excel', [ADMIN\AcnooRentPaymentReportController::class, 'exportExcel'])->name('rent-payment-reports.excel');
Route::get('rent-payment-reports-csv', [ADMIN\AcnooRentPaymentReportController::class, 'exportCsv'])->name('rent-payment-reports.csv');

Route::resource('security-deposit-reports', Admin\AcnooSecurityDepositReportController::class)->only('index', 'show');
Route::post('security-deposit-reports/filter', [ADMIN\AcnooSecurityDepositReportController::class, 'acnooFilter'])->name('security-deposit-reports.filter');
Route::get('security-deposit-reports-excel', [ADMIN\AcnooSecurityDepositReportController::class, 'exportExcel'])->name('security-deposit-reports.excel');
Route::get('security-deposit-reports-csv', [ADMIN\AcnooSecurityDepositReportController::class, 'exportCsv'])->name('security-deposit-reports.csv');

Route::resource('subscription-reports', Admin\AcnooSubscriptionReportController::class)->only('index');
Route::post('subscription-reports/filter', [ADMIN\AcnooSubscriptionReportController::class, 'acnooFilter'])->name('subscription-reports.filter');
Route::get('subscription-reports-excel', [ADMIN\AcnooSubscriptionReportController::class, 'exportExcel'])->name('subscription-reports.excel');
Route::get('subscription-reports-csv', [ADMIN\AcnooSubscriptionReportController::class, 'exportCsv'])->name('subscription-reports.csv');

Route::resource('transaction-reports', Admin\AcnooTransactionReportController::class)->only('index');
Route::post('transaction-reports/filter', [ADMIN\AcnooTransactionReportController::class, 'acnooFilter'])->name('transaction-reports.filter');
Route::get('transaction-reports-excel', [ADMIN\AcnooTransactionReportController::class, 'exportExcel'])->name('transaction-reports.excel');
Route::get('transaction-reports-csv', [ADMIN\AcnooTransactionReportController::class, 'exportCsv'])->name('transaction-reports.csv');

Route::resource('supports', Admin\AcnooSupportController::class)->only('index');
Route::get('supports/get-details-msg/{id}', [Admin\AcnooSupportController::class, 'getDetailsMsg'])->name('supports.getDetailsMsg');
Route::post('supports/reply', [Admin\AcnooSupportController::class, 'reply'])->name('supports.reply');
Route::post('supports/filter', [ADMIN\AcnooSupportController::class, 'acnooFilter'])->name('supports.filter');

//Income Category
Route::resource('income-categories', Admin\AcnooIncomeCategoryController::class)->except('show');
Route::post('income-categories/filter', [ADMIN\AcnooIncomeCategoryController::class, 'acnooFilter'])->name('income-categories.filter');
Route::post('income-categories/delete-all', [ADMIN\AcnooIncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');
Route::post('income-categories/status/{id}', [ADMIN\AcnooIncomeCategoryController::class, 'status'])->name('income-categories.status');
Route::post('income-categories/delete-all', [ADMIN\AcnooIncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');

//Income
Route::resource('incomes', Admin\AcnooIncomeController::class)->except('show');
Route::post('incomes/filter', [ADMIN\AcnooIncomeController::class, 'acnooFilter'])->name('incomes.filter');
Route::post('incomes/delete-all', [ADMIN\AcnooIncomeController::class, 'deleteAll'])->name('incomes.delete-all');
Route::get('incomes-excel', [ADMIN\AcnooIncomeController::class, 'exportExcel'])->name('incomes.excel');
Route::get('incomes-csv', [ADMIN\AcnooIncomeController::class, 'exportCsv'])->name('incomes.csv');
Route::post('incomes/status/{id}', [ADMIN\AcnooIncomeController::class, 'status'])->name('incomes.status');

// Expenses Category
Route::resource('expense-categories', Admin\AcnooExpenseCategoryController::class);
Route::post('expense-categories/filter', [ADMIN\AcnooExpenseCategoryController::class, 'acnooFilter'])->name('expense-categories.filter');
Route::post('expense-categories/delete-all', [ADMIN\AcnooExpenseCategoryController::class, 'deleteAll'])->name('expense-categories.delete-all');
Route::post('expense-categories/status/{id}', [Admin\AcnooExpenseCategoryController::class, 'status'])->name('expense-categories.status');

// Expenses
Route::resource('expenses', Admin\AcnooExpenseController::class);
Route::post('expenses/filter', [ADMIN\AcnooExpenseController::class, 'acnooFilter'])->name('expenses.filter');
Route::post('expenses/delete-all', [ADMIN\AcnooExpenseController::class, 'deleteAll'])->name('expenses.delete-all');
Route::get('expenses-excel', [ADMIN\AcnooExpenseController::class, 'exportExcel'])->name('expenses.excel');
Route::get('expenses-csv', [ADMIN\AcnooExpenseController::class, 'exportCsv'])->name('expenses.csv');
Route::post('expenses/status/{id}', [Admin\AcnooExpenseController::class, 'status'])->name('expenses.status');

// Message
Route::resource('messages', ADMIN\AcnooMessageController::class)->only('index', 'destroy');
Route::post('messages/filter', [ADMIN\AcnooMessageController::class, 'acnooFilter'])->name('messages.filter');
Route::post('messages/delete-all', [ADMIN\AcnooMessageController::class, 'deleteAll'])->name('messages.delete-all');

// Front CMS
Route::resource('website-settings',ADMIN\AcnooWebSettingController::class);

// Term And Condition Controller
Route::resource('term-conditions', ADMIN\AcnooTermConditionController::class)->only('index', 'store');

// Term And Condition Controller
Route::resource('privacy-policy', ADMIN\AcnooPrivacyPolicyController::class)->only('index', 'store');

Route::resource('testimonials', ADMIN\AcnooTestimonialController::class)->except('show');
Route::post('testimonials/filter', [ADMIN\AcnooTestimonialController::class, 'acnooFilter'])->name('testimonials.filter');
Route::post('testimonials/delete-all', [ADMIN\AcnooTestimonialController::class, 'deleteAll'])->name('testimonials.delete-all');

Route::resource('features',ADMIN\AcnooFeatureController::class);
Route::post('features/filter', [ADMIN\AcnooFeatureController::class, 'acnooFilter'])->name('features.filter');
Route::post('features/status/{id}', [ADMIN\AcnooFeatureController::class,'status'])->name('features.status');
Route::post('features/delete-all', [ADMIN\AcnooFeatureController::class, 'deleteAll'])->name('features.delete-all');

Route::resource('clients',ADMIN\AcnooClientController::class);
Route::post('clients/filter', [ADMIN\AcnooClientController::class, 'acnooFilter'])->name('clients.filter');
Route::post('clients/status/{id}', [ADMIN\AcnooClientController::class,'status'])->name('clients.status');
Route::post('clients/delete-all', [ADMIN\AcnooClientController::class, 'deleteAll'])->name('clients.delete-all');

Route::resource('blogs', ADMIN\AcnooBlogController::class);
Route::post('blogs/filter', [ADMIN\AcnooBlogController::class, 'acnooFilter'])->name('blogs.filter');
Route::post('blogs/status/{id}', [ADMIN\AcnooBlogController::class,'status'])->name('blogs.status');
Route::post('blogs/delete-all', [ADMIN\AcnooBlogController::class, 'deleteAll'])->name('blogs.delete-all');
Route::get('blogs/comments/{id}', [ADMIN\AcnooBlogController::class, 'filterComment'])->name('blogs.filter.comment');

Route::resource('highlights', ADMIN\AcnooHighlightController::class);
Route::post('highlights/filter', [ADMIN\AcnooHighlightController::class, 'acnooFilter'])->name('highlights.filter');
Route::post('highlights/status/{id}', [ADMIN\AcnooHighlightController::class,'status'])->name('highlights.status');
Route::post('highlights/delete-all', [ADMIN\AcnooHighlightController::class, 'deleteAll'])->name('highlights.delete-all');

Route::resource('comments', ADMIN\AcnooCommentController::class);
Route::post('comments/delete-all', [ADMIN\AcnooCommentController::class, 'deleteAll'])->name('comments.delete-all');
Route::post('comments/filter', [ADMIN\AcnooCommentController::class, 'acnooFilter'])->name('comments.filter');

Route::resource('faqs', ADMIN\AcnooFaqController::class)->except('show');
Route::post('faqs/delete-all', [ADMIN\AcnooFaqController::class, 'deleteAll'])->name('faqs.delete-all');
Route::post('faqs/filter', [ADMIN\AcnooFaqController::class, 'acnooFilter'])->name('faqs.filter');
Route::post('faqs/status/{id}', [ADMIN\AcnooFaqController::class,'status'])->name('faqs.status');

Route::resource('manage-settings', ADMIN\AcnooSettingsManagerController::class)->only('index', 'store');

Route::resource('languages', ADMIN\AcnooLanguageController::class)->except('edit', 'create');
Route::post('languages/filter', [ADMIN\AcnooLanguageController::class, 'acnooFilter'])->name('languages.filter');
Route::post('languages/delete-all', [ADMIN\AcnooLanguageController::class, 'deleteAll'])->name('languages.delete-all');
Route::post('languages/status/{id}', [ADMIN\AcnooLanguageController::class, 'status'])->name('languages.status');
Route::post('languages/default/{id}', [ADMIN\AcnooLanguageController::class, 'isDefault'])->name('languages.default');

Route::resource('translations', ADMIN\TranslationController::class)->only('store', 'update', 'destroy');
Route::post('translations/bulk-update/{lang}', [ADMIN\TranslationController::class, 'bulkUpdate'])->name('translations.bulk-update');

// Notifications manager
Route::prefix('notifications')->controller(ADMIN\NotificationController::class)->name('notifications.')->group(function () {
    Route::get('/', 'mtIndex')->name('index');
    Route::get('/{id}', 'mtView')->name('mtView');
    Route::get('view/all/', 'mtReadAll')->name('mtReadAll');
    Route::post('notification/filter/', 'acnooFilter')->name('filter');
});
