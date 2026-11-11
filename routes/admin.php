<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::get('/', [Admin\AcnooAdminDashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard-data', [Admin\AcnooAdminDashboardController::class, 'getDashboardData'])->name('getDashboardData');
Route::get('/property-chart', [Admin\AcnooAdminDashboardController::class, 'property_chart'])->name('getPropertyData');
Route::get('/account-data', [Admin\AcnooAdminDashboardController::class, 'account_chart'])->name('getAccountData');

Route::resource('profile', Admin\AcnooProfileController::class)->only('index', 'update');

Route::resource('users', Admin\UserController::class);
Route::post('users/filter', [Admin\UserController::class, 'acnooFilter'])->name('users.filter');
Route::post('users/status/{id}', [Admin\UserController::class, 'status'])->name('users.status');
Route::post('users/delete-all', [Admin\UserController::class, 'deleteAll'])->name('users.delete-all');

Route::resource('properties', Admin\PropertyController::class);
Route::post('properties/status/{id}', [Admin\PropertyController::class, 'status'])->name('properties.status');
Route::get('properties/success/{id}', [Admin\PropertyController::class, 'propertySuccess'])->name('properties.success');
Route::post('properties/filter', [Admin\PropertyController::class, 'acnooFilter'])->name('properties.filter');
Route::post('properties/delete-all', [Admin\PropertyController::class, 'deleteAll'])->name('properties.delete-all');
Route::post('properties/reject/{id}', [Admin\PropertyController::class, 'reject'])->name('properties.reject');
Route::post('properties/approve/{id}', [Admin\PropertyController::class, 'approve'])->name('properties.approve');
Route::get('properties-excel', [Admin\PropertyController::class, 'exportExcel'])->name('properties.excel');
Route::get('properties-csv', [Admin\PropertyController::class, 'exportCsv'])->name('properties.csv');
Route::get('properties/get-facilities/{landlordId}', [Admin\PropertyController::class, 'getFacilities']);
Route::get('properties/get-amenities/{landlordId}', [Admin\PropertyController::class, 'getAmenities']);

Route::resource('landlords', Admin\AcnooLandlordController::class);
Route::post('landlords/filter', [Admin\AcnooLandlordController::class, 'acnooFilter'])->name('landlords.filter');
Route::post('landlords/delete-all', [Admin\AcnooLandlordController::class, 'deleteAll'])->name('landlords.delete-all');
Route::get('landlords-excel', [Admin\AcnooLandlordController::class, 'exportExcel'])->name('landlords.excel');
Route::get('landlords-csv', [Admin\AcnooLandlordController::class, 'exportCsv'])->name('landlords.csv');

Route::resource('tenants', Admin\AcnooTenantController::class);
Route::post('tenants/filter', [Admin\AcnooTenantController::class, 'acnooFilter'])->name('tenants.filter');
Route::post('tenants/delete-all', [Admin\AcnooTenantController::class, 'deleteAll'])->name('tenants.delete-all');
Route::post('tenants/otp-submit', [Admin\AcnooTenantController::class, 'submitOtp'])->name('tenants.otp-submit');
Route::post('tenants/otp-resend', [Admin\AcnooTenantController::class, 'otpResend'])->name('tenants.otp-resend');
Route::get('tenant/excel', [Admin\AcnooTenantController::class, 'exportExcel'])->name('tenants.excel');
Route::get('tenant/csv', [Admin\AcnooTenantController::class, 'exportCsv'])->name('tenants.csv');
Route::get('tenant/file-download/{id}', [Admin\AcnooTenantController::class, 'file_download'])->name('tenants.file-download');

Route::resource('transactions', Admin\AcnooTransactionController::class);
Route::post('transactions/filter', [Admin\AcnooTransactionController::class, 'acnooFilter'])->name('transactions.filter');
Route::get('transactions-excel', [Admin\AcnooTransactionController::class, 'exportExcel'])->name('transactions.excel');
Route::get('transactions-csv', [Admin\AcnooTransactionController::class, 'exportCsv'])->name('transactions.csv');

Route::resource('gateways', Admin\AcnooGatewayController::class)->only('index', 'update');

// Route::resource('banner', Admin\BannerController::class)->only(['index', 'create', 'update']);
Route::resource('service-charges', Admin\ServiceChargeController::class)->only('index', 'update');

Route::resource('refund-reason', Admin\AcnooRefundReasonController::class);
Route::post('refund-reason/filter', [Admin\AcnooRefundReasonController::class, 'acnooFilter'])->name('refund-reason.filter');
Route::post('refund-reason/delete-all', [Admin\AcnooRefundReasonController::class, 'deleteAll'])->name('refund-reason.delete-all');
Route::post('refund-reason/status/{id}', [Admin\AcnooRefundReasonController::class, 'status'])->name('refund-reason.status');

Route::resource('plans', Admin\PlanController::class)->except('show');

// House Type
Route::resource('house-types', Admin\AcnooHouseTypeController::class);
Route::post('house-types/filter', [Admin\AcnooHouseTypeController::class, 'acnooFilter'])->name('house-types.filter');
Route::post('house-types/delete-all', [Admin\AcnooHouseTypeController::class, 'deleteAll'])->name('house-types.delete-all');
Route::post('house-types/status/{id}', [Admin\AcnooHouseTypeController::class, 'status'])->name('house-types.status');

// Facility
Route::resource('facilities', Admin\AcnooFacilityController::class);
Route::post('facilities/filter', [Admin\AcnooFacilityController::class, 'acnooFilter'])->name('facilities.filter');
Route::post('facilities/delete-all', [Admin\AcnooFacilityController::class, 'deleteAll'])->name('facilities.delete-all');
Route::post('facilities/status/{id}', [Admin\AcnooFacilityController::class, 'status'])->name('facilities.status');

// Amenity
Route::resource('amenities', Admin\AcnooAmenityController::class);
Route::post('amenities/filter', [Admin\AcnooAmenityController::class, 'acnooFilter'])->name('amenities.filter');
Route::post('amenities/delete-all', [Admin\AcnooAmenityController::class, 'deleteAll'])->name('amenities.delete-all');
Route::post('amenities/status/{id}', [Admin\AcnooAmenityController::class, 'status'])->name('amenities.status');

// Withdrawal Method
Route::resource('withdraw-methods', Admin\AcnooWithdrawMethodController::class);
Route::post('withdraw-methods/status/{id}', [Admin\AcnooWithdrawMethodController::class, 'status'])->name('withdraw-methods.status');
Route::post('withdraw-methods/filter', [Admin\AcnooWithdrawMethodController::class, 'acnooFilter'])->name('withdraw-methods.filter');
Route::post('withdraw-methods/delete-all', [Admin\AcnooWithdrawMethodController::class, 'deleteAll'])->name('withdraw-methods.delete-all');
Route::get('withdraw-methods-excel', [Admin\AcnooWithdrawMethodController::class, 'exportExcel'])->name('withdraw-methods.excel');
Route::get('withdraw-methods-csv', [Admin\AcnooWithdrawMethodController::class, 'exportCsv'])->name('withdraw-methods.csv');

// Withdrawal
Route::resource('withdraw-request', Admin\AcnooWithdrawRequestController::class)->only('index', 'show', 'store', 'reject');
Route::post('withdraw-request/filter', [Admin\AcnooWithdrawRequestController::class, 'acnooFilter'])->name('withdraw-request.filter');
Route::post('withdraw-request/reject/{id}', [Admin\AcnooWithdrawRequestController::class, 'reject'])->name('withdraw.reject');
Route::post('withdraw-request/approve/{id}', [Admin\AcnooWithdrawRequestController::class, 'approve'])->name('withdraw.approve');
Route::get('withdraw-request-excel', [Admin\AcnooWithdrawRequestController::class, 'exportExcel'])->name('withdraw-request.excel');
Route::get('withdraw-request-csv', [Admin\AcnooWithdrawRequestController::class, 'exportCsv'])->name('withdraw-request.csv');

Route::resource('rent-payments', Admin\AcnooRentPaymentController::class)->only('index');
Route::post('rent-payments/filter', [Admin\AcnooRentPaymentController::class, 'acnooFilter'])->name('rent-payments.filter');
Route::post('rent-payments/approve/{id}', [Admin\AcnooRentPaymentController::class, 'approve'])->name('rent-payments.approve');
Route::post('rent-payments/rejected/{id}', [Admin\AcnooRentPaymentController::class, 'rejected'])->name('rent-payments.reject');
Route::get('rent-payments/file-download/{id}', [Admin\AcnooRentPaymentController::class, 'file_download'])->name('rent-payments.download');
Route::get('rent-payments-excel', [Admin\AcnooRentPaymentController::class, 'exportExcel'])->name('rent-payments.excel');
Route::get('rent-payments-csv', [Admin\AcnooRentPaymentController::class, 'exportCsv'])->name('rent-payments.csv');

Route::resource('security-deposits', Admin\AcnooSecurityDepositController::class)->only('index');
Route::post('security-deposits/filter', [Admin\AcnooSecurityDepositController::class, 'acnooFilter'])->name('security-deposits.filter');
Route::post('security-deposits/approve/{id}', [Admin\AcnooSecurityDepositController::class, 'approve'])->name('security-deposits.approve');
Route::post('security-deposits/rejected/{id}', [Admin\AcnooSecurityDepositController::class, 'rejected'])->name('security-deposits.reject');
Route::get('security-deposits/file-download/{id}', [Admin\AcnooSecurityDepositController::class, 'file_download'])->name('security-deposits.download');
Route::get('security-deposits-excel', [Admin\AcnooSecurityDepositController::class, 'exportExcel'])->name('security-deposits.excel');
Route::get('security-deposits-csv', [Admin\AcnooSecurityDepositController::class, 'exportCsv'])->name('security-deposits.csv');

Route::resource('rent-payment-generate', Admin\AcnooRentPaymentGenerateController::class);

// Reminder Method
Route::resource('reminder-messages', Admin\AcnooReminderMessageController::class)->except('show');

// Roles & Permissions
Route::resource('roles', Admin\RoleController::class)->except('show');
Route::resource('permissions', Admin\PermissionController::class)->only('index', 'store');

// General Setting
Route::resource('settings', Admin\SettingController::class)->only('index', 'update');

// System Setting
Route::resource('system-settings', Admin\SystemSettingController::class)->only('index', 'store');

// Currencies
Route::resource('currencies', Admin\AcnooCurrencyController::class)->except('show');
Route::post('currencies/filter', [Admin\AcnooCurrencyController::class, 'acnooFilter'])->name('currencies.filter');
Route::match(['get', 'post'], 'currencies/default/{id}', [Admin\AcnooCurrencyController::class, 'default'])->name('currencies.default');
Route::post('currencies/delete-all', [Admin\AcnooCurrencyController::class, 'deleteAll'])->name('currencies.delete-all');

// Subscriptions
Route::resource('subscriptions', Admin\AcnooSubscriptionController::class)->only('index');
Route::post('subscriptions/filter', [Admin\AcnooSubscriptionController::class, 'acnooFilter'])->name('subscriptions.filter');
Route::post('subscriptions/reject/{id}', [Admin\AcnooSubscriptionController::class, 'reject'])->name('subscriptions.reject');
Route::post('subscriptions/paid/{id}', [Admin\AcnooSubscriptionController::class, 'paid'])->name('subscriptions.paid');
Route::get('subscriptions/get-invoice/{id}', [Admin\AcnooSubscriptionController::class, 'getInvoice'])->name('subscriptions.invoice');
Route::get('subscriptions-excel', [Admin\AcnooSubscriptionController::class, 'exportExcel'])->name('subscriptions.excel');
Route::get('subscriptions-csv', [Admin\AcnooSubscriptionController::class, 'exportCsv'])->name('subscriptions.csv');
Route::get('subscriptions/file-download/{id}', [Admin\AcnooSubscriptionController::class, 'file_download'])->name('subscriptions.download');

// Report
Route::resource('refund-request', Admin\AcnooRefundRequestController::class)->only('index', 'show');
Route::post('refund-request/filter', [Admin\AcnooRefundRequestController::class, 'acnooFilter'])->name('refund-request.filter');
Route::get('refund-request-reports-excel', [Admin\AcnooRefundRequestController::class, 'exportExcel'])->name('refund-request-reports.excel');
Route::get('refund-request-reports-csv', [Admin\AcnooRefundRequestController::class, 'exportCsv'])->name('refund-request-reports.csv');

Route::resource('landlord-withdraws', Admin\AcnooLandlordWithdrawReportController::class)->only('index', 'show');
Route::post('landlord-withdraws/filter', [Admin\AcnooLandlordWithdrawReportController::class, 'acnooFilter'])->name('landlord-withdraws.filter');
Route::get('landlord-withdraws-excel', [Admin\AcnooLandlordWithdrawReportController::class, 'exportExcel'])->name('landlord-withdraws.excel');
Route::get('landlord-withdraws-csv', [Admin\AcnooLandlordWithdrawReportController::class, 'exportCsv'])->name('landlord-withdraws.csv');

Route::resource('rent-payment-reports', Admin\AcnooRentPaymentReportController::class)->only('index', 'show');
Route::post('rent-payment-reports/filter', [Admin\AcnooRentPaymentReportController::class, 'acnooFilter'])->name('rent-payment-reports.filter');
Route::get('rent-payment-reports-excel', [Admin\AcnooRentPaymentReportController::class, 'exportExcel'])->name('rent-payment-reports.excel');
Route::get('rent-payment-reports-csv', [Admin\AcnooRentPaymentReportController::class, 'exportCsv'])->name('rent-payment-reports.csv');

Route::resource('security-deposit-reports', Admin\AcnooSecurityDepositReportController::class)->only('index', 'show');
Route::post('security-deposit-reports/filter', [Admin\AcnooSecurityDepositReportController::class, 'acnooFilter'])->name('security-deposit-reports.filter');
Route::get('security-deposit-reports-excel', [Admin\AcnooSecurityDepositReportController::class, 'exportExcel'])->name('security-deposit-reports.excel');
Route::get('security-deposit-reports-csv', [Admin\AcnooSecurityDepositReportController::class, 'exportCsv'])->name('security-deposit-reports.csv');

Route::resource('subscription-reports', Admin\AcnooSubscriptionReportController::class)->only('index');
Route::post('subscription-reports/filter', [Admin\AcnooSubscriptionReportController::class, 'acnooFilter'])->name('subscription-reports.filter');
Route::get('subscription-reports-excel', [Admin\AcnooSubscriptionReportController::class, 'exportExcel'])->name('subscription-reports.excel');
Route::get('subscription-reports-csv', [Admin\AcnooSubscriptionReportController::class, 'exportCsv'])->name('subscription-reports.csv');

Route::resource('transaction-reports', Admin\AcnooTransactionReportController::class)->only('index');
Route::post('transaction-reports/filter', [Admin\AcnooTransactionReportController::class, 'acnooFilter'])->name('transaction-reports.filter');
Route::get('transaction-reports-excel', [Admin\AcnooTransactionReportController::class, 'exportExcel'])->name('transaction-reports.excel');
Route::get('transaction-reports-csv', [Admin\AcnooTransactionReportController::class, 'exportCsv'])->name('transaction-reports.csv');

Route::resource('supports', Admin\AcnooSupportController::class)->only('index');
Route::get('supports/get-details-msg/{id}', [Admin\AcnooSupportController::class, 'getDetailsMsg'])->name('supports.getDetailsMsg');
Route::post('supports/reply', [Admin\AcnooSupportController::class, 'reply'])->name('supports.reply');
Route::post('supports/filter', [Admin\AcnooSupportController::class, 'acnooFilter'])->name('supports.filter');

// Income Category
Route::resource('income-categories', Admin\AcnooIncomeCategoryController::class)->except('show');
Route::post('income-categories/filter', [Admin\AcnooIncomeCategoryController::class, 'acnooFilter'])->name('income-categories.filter');
Route::post('income-categories/delete-all', [Admin\AcnooIncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');
Route::post('income-categories/status/{id}', [Admin\AcnooIncomeCategoryController::class, 'status'])->name('income-categories.status');
Route::post('income-categories/delete-all', [Admin\AcnooIncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');

// Income
Route::resource('incomes', Admin\AcnooIncomeController::class)->except('show');
Route::post('incomes/filter', [Admin\AcnooIncomeController::class, 'acnooFilter'])->name('incomes.filter');
Route::post('incomes/delete-all', [Admin\AcnooIncomeController::class, 'deleteAll'])->name('incomes.delete-all');
Route::get('incomes-excel', [Admin\AcnooIncomeController::class, 'exportExcel'])->name('incomes.excel');
Route::get('incomes-csv', [Admin\AcnooIncomeController::class, 'exportCsv'])->name('incomes.csv');
Route::post('incomes/status/{id}', [Admin\AcnooIncomeController::class, 'status'])->name('incomes.status');

// Expenses Category
Route::resource('expense-categories', Admin\AcnooExpenseCategoryController::class);
Route::post('expense-categories/filter', [Admin\AcnooExpenseCategoryController::class, 'acnooFilter'])->name('expense-categories.filter');
Route::post('expense-categories/delete-all', [Admin\AcnooExpenseCategoryController::class, 'deleteAll'])->name('expense-categories.delete-all');
Route::post('expense-categories/status/{id}', [Admin\AcnooExpenseCategoryController::class, 'status'])->name('expense-categories.status');

// Expenses
Route::resource('expenses', Admin\AcnooExpenseController::class);
Route::post('expenses/filter', [Admin\AcnooExpenseController::class, 'acnooFilter'])->name('expenses.filter');
Route::post('expenses/delete-all', [Admin\AcnooExpenseController::class, 'deleteAll'])->name('expenses.delete-all');
Route::get('expenses-excel', [Admin\AcnooExpenseController::class, 'exportExcel'])->name('expenses.excel');
Route::get('expenses-csv', [Admin\AcnooExpenseController::class, 'exportCsv'])->name('expenses.csv');
Route::post('expenses/status/{id}', [Admin\AcnooExpenseController::class, 'status'])->name('expenses.status');

// Message
Route::resource('messages', Admin\AcnooMessageController::class)->only('index', 'destroy');
Route::post('messages/filter', [Admin\AcnooMessageController::class, 'acnooFilter'])->name('messages.filter');
Route::post('messages/delete-all', [Admin\AcnooMessageController::class, 'deleteAll'])->name('messages.delete-all');

// Front CMS
Route::resource('website-settings', Admin\AcnooWebSettingController::class);

// Term And Condition Controller
Route::resource('term-conditions', Admin\AcnooTermConditionController::class)->only('index', 'store');

// Term And Condition Controller
Route::resource('privacy-policy', Admin\AcnooPrivacyPolicyController::class)->only('index', 'store');

Route::resource('testimonials', Admin\AcnooTestimonialController::class)->except('show');
Route::post('testimonials/filter', [Admin\AcnooTestimonialController::class, 'acnooFilter'])->name('testimonials.filter');
Route::post('testimonials/delete-all', [Admin\AcnooTestimonialController::class, 'deleteAll'])->name('testimonials.delete-all');

Route::resource('features', Admin\AcnooFeatureController::class);
Route::post('features/filter', [Admin\AcnooFeatureController::class, 'acnooFilter'])->name('features.filter');
Route::post('features/status/{id}', [Admin\AcnooFeatureController::class, 'status'])->name('features.status');
Route::post('features/delete-all', [Admin\AcnooFeatureController::class, 'deleteAll'])->name('features.delete-all');

Route::resource('clients', Admin\AcnooClientController::class);
Route::post('clients/filter', [Admin\AcnooClientController::class, 'acnooFilter'])->name('clients.filter');
Route::post('clients/status/{id}', [Admin\AcnooClientController::class, 'status'])->name('clients.status');
Route::post('clients/delete-all', [Admin\AcnooClientController::class, 'deleteAll'])->name('clients.delete-all');

Route::resource('blogs', Admin\AcnooBlogController::class);
Route::post('blogs/filter', [Admin\AcnooBlogController::class, 'acnooFilter'])->name('blogs.filter');
Route::post('blogs/status/{id}', [Admin\AcnooBlogController::class, 'status'])->name('blogs.status');
Route::post('blogs/delete-all', [Admin\AcnooBlogController::class, 'deleteAll'])->name('blogs.delete-all');
Route::get('blogs/comments/{id}', [Admin\AcnooBlogController::class, 'filterComment'])->name('blogs.filter.comment');

Route::resource('highlights', Admin\AcnooHighlightController::class);
Route::post('highlights/filter', [Admin\AcnooHighlightController::class, 'acnooFilter'])->name('highlights.filter');
Route::post('highlights/status/{id}', [Admin\AcnooHighlightController::class, 'status'])->name('highlights.status');
Route::post('highlights/delete-all', [Admin\AcnooHighlightController::class, 'deleteAll'])->name('highlights.delete-all');

Route::resource('comments', Admin\AcnooCommentController::class);
Route::post('comments/delete-all', [Admin\AcnooCommentController::class, 'deleteAll'])->name('comments.delete-all');
Route::post('comments/filter', [Admin\AcnooCommentController::class, 'acnooFilter'])->name('comments.filter');

Route::resource('faqs', Admin\AcnooFaqController::class)->except('show');
Route::post('faqs/delete-all', [Admin\AcnooFaqController::class, 'deleteAll'])->name('faqs.delete-all');
Route::post('faqs/filter', [Admin\AcnooFaqController::class, 'acnooFilter'])->name('faqs.filter');
Route::post('faqs/status/{id}', [Admin\AcnooFaqController::class, 'status'])->name('faqs.status');

Route::resource('manage-settings', Admin\AcnooSettingsManagerController::class)->only('index', 'store');

Route::resource('languages', Admin\AcnooLanguageController::class)->except('edit', 'create');
Route::post('languages/filter', [Admin\AcnooLanguageController::class, 'acnooFilter'])->name('languages.filter');
Route::post('languages/delete-all', [Admin\AcnooLanguageController::class, 'deleteAll'])->name('languages.delete-all');
Route::post('languages/status/{id}', [Admin\AcnooLanguageController::class, 'status'])->name('languages.status');
Route::post('languages/default/{id}', [Admin\AcnooLanguageController::class, 'isDefault'])->name('languages.default');

Route::resource('translations', Admin\TranslationController::class)->only('store', 'update', 'destroy');
Route::post('translations/bulk-update/{lang}', [Admin\TranslationController::class, 'bulkUpdate'])->name('translations.bulk-update');

// Notifications manager
Route::prefix('notifications')->controller(Admin\NotificationController::class)->name('notifications.')->group(function () {
    Route::get('/', 'mtIndex')->name('index');
    Route::get('/{id}', 'mtView')->name('mtView');
    Route::get('view/all/', 'mtReadAll')->name('mtReadAll');
    Route::post('notification/filter/', 'acnooFilter')->name('filter');
});
