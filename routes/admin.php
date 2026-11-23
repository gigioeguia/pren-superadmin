<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::get('/', [Admin\AdminDashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard-data', [Admin\AdminDashboardController::class, 'getDashboardData'])->name('getDashboardData');
Route::get('/property-chart', [Admin\AdminDashboardController::class, 'property_chart'])->name('getPropertyData');
Route::get('/account-data', [Admin\AdminDashboardController::class, 'account_chart'])->name('getAccountData');

Route::resource('profile', Admin\ProfileController::class)->only('index', 'update');

Route::resource('users', Admin\UserController::class)->except('show');
Route::post('users/filter', [Admin\UserController::class, 'filter'])->name('users.filter');
Route::post('users/status/{id}', [Admin\UserController::class, 'status'])->name('users.status');
Route::post('users/delete-all', [Admin\UserController::class, 'deleteAll'])->name('users.delete-all');

Route::resource('properties', Admin\PropertyController::class);
Route::post('properties/status/{id}', [Admin\PropertyController::class, 'status'])->name('properties.status');
Route::get('properties/success/{id}', [Admin\PropertyController::class, 'propertySuccess'])->name('properties.success');
Route::post('properties/filter', [Admin\PropertyController::class, 'filter'])->name('properties.filter');
Route::post('properties/delete-all', [Admin\PropertyController::class, 'deleteAll'])->name('properties.delete-all');
Route::post('properties/reject/{id}', [Admin\PropertyController::class, 'reject'])->name('properties.reject');
Route::post('properties/approve/{id}', [Admin\PropertyController::class, 'approve'])->name('properties.approve');
Route::get('properties-excel', [Admin\PropertyController::class, 'exportExcel'])->name('properties.excel');
Route::get('properties-csv', [Admin\PropertyController::class, 'exportCsv'])->name('properties.csv');
Route::get('properties/get-facilities/{landlordId}', [Admin\PropertyController::class, 'getFacilities'])->name('properties.get-facilities');
Route::get('properties/get-amenities/{landlordId}', [Admin\PropertyController::class, 'getAmenities'])->name('properties.get-amenities');

Route::resource('landlords', Admin\LandlordController::class)->except('show');
Route::post('landlords/filter', [Admin\LandlordController::class, 'filter'])->name('landlords.filter');
Route::post('landlords/delete-all', [Admin\LandlordController::class, 'deleteAll'])->name('landlords.delete-all');
Route::get('landlords-excel', [Admin\LandlordController::class, 'exportExcel'])->name('landlords.excel');
Route::get('landlords-csv', [Admin\LandlordController::class, 'exportCsv'])->name('landlords.csv');

Route::resource('tenants', Admin\TenantController::class);
Route::post('tenants/filter', [Admin\TenantController::class, 'filter'])->name('tenants.filter');
Route::post('tenants/delete-all', [Admin\TenantController::class, 'deleteAll'])->name('tenants.delete-all');
Route::get('tenant/excel', [Admin\TenantController::class, 'exportExcel'])->name('tenants.excel');
Route::get('tenant/csv', [Admin\TenantController::class, 'exportCsv'])->name('tenants.csv');
Route::get('tenant/file-download/{id}', [Admin\TenantController::class, 'file_download'])->name('tenants.file-download');

Route::resource('transactions', Admin\TransactionController::class)->only('index');
Route::post('transactions/filter', [Admin\TransactionController::class, 'filter'])->name('transactions.filter');
Route::get('transactions-excel', [Admin\TransactionController::class, 'exportExcel'])->name('transactions.excel');
Route::get('transactions-csv', [Admin\TransactionController::class, 'exportCsv'])->name('transactions.csv');

Route::resource('gateways', Admin\GatewayController::class)->only('index', 'update');

// Route::resource('banner', Admin\BannerController::class)->only(['index', 'create', 'update']);
Route::resource('service-charges', Admin\ServiceChargeController::class)->only('index', 'update');

Route::resource('refund-reason', Admin\RefundReasonController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('refund-reason/filter', [Admin\RefundReasonController::class, 'filter'])->name('refund-reason.filter');
Route::post('refund-reason/delete-all', [Admin\RefundReasonController::class, 'deleteAll'])->name('refund-reason.delete-all');
Route::post('refund-reason/status/{id}', [Admin\RefundReasonController::class, 'status'])->name('refund-reason.status');

Route::resource('plans', Admin\PlanController::class)->except('show');

// House Type
Route::resource('house-types', Admin\HouseTypeController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('house-types/filter', [Admin\HouseTypeController::class, 'filter'])->name('house-types.filter');
Route::post('house-types/delete-all', [Admin\HouseTypeController::class, 'deleteAll'])->name('house-types.delete-all');
Route::post('house-types/status/{id}', [Admin\HouseTypeController::class, 'status'])->name('house-types.status');

// Facility
Route::resource('facilities', Admin\FacilityController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('facilities/filter', [Admin\FacilityController::class, 'filter'])->name('facilities.filter');
Route::post('facilities/delete-all', [Admin\FacilityController::class, 'deleteAll'])->name('facilities.delete-all');
Route::post('facilities/status/{id}', [Admin\FacilityController::class, 'status'])->name('facilities.status');

// Amenity
Route::resource('amenities', Admin\AmenityController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('amenities/filter', [Admin\AmenityController::class, 'filter'])->name('amenities.filter');
Route::post('amenities/delete-all', [Admin\AmenityController::class, 'deleteAll'])->name('amenities.delete-all');
Route::post('amenities/status/{id}', [Admin\AmenityController::class, 'status'])->name('amenities.status');

// Withdrawal Method
Route::resource('withdraw-methods', Admin\WithdrawMethodController::class)->except('show');
Route::post('withdraw-methods/status/{id}', [Admin\WithdrawMethodController::class, 'status'])->name('withdraw-methods.status');
Route::post('withdraw-methods/filter', [Admin\WithdrawMethodController::class, 'filter'])->name('withdraw-methods.filter');
Route::post('withdraw-methods/delete-all', [Admin\WithdrawMethodController::class, 'deleteAll'])->name('withdraw-methods.delete-all');
Route::get('withdraw-methods-excel', [Admin\WithdrawMethodController::class, 'exportExcel'])->name('withdraw-methods.excel');
Route::get('withdraw-methods-csv', [Admin\WithdrawMethodController::class, 'exportCsv'])->name('withdraw-methods.csv');

// Withdrawal
Route::resource('withdraw-request', Admin\WithdrawRequestController::class)->only('index', 'show');
Route::post('withdraw-request/filter', [Admin\WithdrawRequestController::class, 'filter'])->name('withdraw-request.filter');
Route::post('withdraw-request/reject/{id}', [Admin\WithdrawRequestController::class, 'reject'])->name('withdraw.reject');
Route::post('withdraw-request/approve/{id}', [Admin\WithdrawRequestController::class, 'approve'])->name('withdraw.approve');
Route::get('withdraw-request-excel', [Admin\WithdrawRequestController::class, 'exportExcel'])->name('withdraw-request.excel');
Route::get('withdraw-request-csv', [Admin\WithdrawRequestController::class, 'exportCsv'])->name('withdraw-request.csv');

Route::resource('rent-payments', Admin\RentPaymentController::class)->only('index');
Route::post('rent-payments/filter', [Admin\RentPaymentController::class, 'filter'])->name('rent-payments.filter');
Route::post('rent-payments/approve/{id}', [Admin\RentPaymentController::class, 'approve'])->name('rent-payments.approve');
Route::post('rent-payments/rejected/{id}', [Admin\RentPaymentController::class, 'rejected'])->name('rent-payments.reject');
Route::get('rent-payments/file-download/{id}', [Admin\RentPaymentController::class, 'file_download'])->name('rent-payments.download');
Route::get('rent-payments-excel', [Admin\RentPaymentController::class, 'exportExcel'])->name('rent-payments.excel');
Route::get('rent-payments-csv', [Admin\RentPaymentController::class, 'exportCsv'])->name('rent-payments.csv');

Route::resource('security-deposits', Admin\SecurityDepositController::class)->only('index');
Route::post('security-deposits/filter', [Admin\SecurityDepositController::class, 'filter'])->name('security-deposits.filter');
Route::post('security-deposits/approve/{id}', [Admin\SecurityDepositController::class, 'approve'])->name('security-deposits.approve');
Route::post('security-deposits/rejected/{id}', [Admin\SecurityDepositController::class, 'rejected'])->name('security-deposits.reject');
Route::get('security-deposits/file-download/{id}', [Admin\SecurityDepositController::class, 'file_download'])->name('security-deposits.download');
Route::get('security-deposits-excel', [Admin\SecurityDepositController::class, 'exportExcel'])->name('security-deposits.excel');
Route::get('security-deposits-csv', [Admin\SecurityDepositController::class, 'exportCsv'])->name('security-deposits.csv');

Route::resource('rent-payment-generate', Admin\RentPaymentGenerateController::class)->only('index', 'store');

// Reminder Method
Route::resource('reminder-messages', Admin\ReminderMessageController::class)->only('index', 'store', 'update', 'destroy');

// Roles & Permissions
Route::resource('roles', Admin\RoleController::class)->except('show');
Route::resource('permissions', Admin\PermissionController::class)->only('index', 'store');

// General Setting
Route::resource('settings', Admin\SettingController::class)->only('index', 'update');

// System Setting
Route::resource('system-settings', Admin\SystemSettingController::class)->only('index', 'store');

// Currencies
Route::resource('currencies', Admin\CurrencyController::class)->except('show');
Route::post('currencies/filter', [Admin\CurrencyController::class, 'filter'])->name('currencies.filter');
Route::match(['get', 'post'], 'currencies/default/{id}', [Admin\CurrencyController::class, 'default'])->name('currencies.default');
Route::post('currencies/delete-all', [Admin\CurrencyController::class, 'deleteAll'])->name('currencies.delete-all');

// Subscriptions
Route::resource('subscriptions', Admin\SubscriptionController::class)->only('index');
Route::post('subscriptions/filter', [Admin\SubscriptionController::class, 'filter'])->name('subscriptions.filter');
Route::post('subscriptions/reject/{id}', [Admin\SubscriptionController::class, 'reject'])->name('subscriptions.reject');
Route::post('subscriptions/paid/{id}', [Admin\SubscriptionController::class, 'paid'])->name('subscriptions.paid');
Route::get('subscriptions/get-invoice/{id}', [Admin\SubscriptionController::class, 'getInvoice'])->name('subscriptions.invoice');
Route::get('subscriptions-excel', [Admin\SubscriptionController::class, 'exportExcel'])->name('subscriptions.excel');
Route::get('subscriptions-csv', [Admin\SubscriptionController::class, 'exportCsv'])->name('subscriptions.csv');
Route::get('subscriptions/file-download/{id}', [Admin\SubscriptionController::class, 'file_download'])->name('subscriptions.download');

// Report
Route::resource('refund-request', Admin\RefundRequestController::class)->only('index', 'show');
Route::post('refund-request/filter', [Admin\RefundRequestController::class, 'filter'])->name('refund-request.filter');
Route::get('refund-request-reports-excel', [Admin\RefundRequestController::class, 'exportExcel'])->name('refund-request-reports.excel');
Route::get('refund-request-reports-csv', [Admin\RefundRequestController::class, 'exportCsv'])->name('refund-request-reports.csv');

Route::resource('landlord-withdraws', Admin\LandlordWithdrawReportController::class)->only('index', 'show');
Route::post('landlord-withdraws/filter', [Admin\LandlordWithdrawReportController::class, 'filter'])->name('landlord-withdraws.filter');
Route::get('landlord-withdraws-excel', [Admin\LandlordWithdrawReportController::class, 'exportExcel'])->name('landlord-withdraws.excel');
Route::get('landlord-withdraws-csv', [Admin\LandlordWithdrawReportController::class, 'exportCsv'])->name('landlord-withdraws.csv');

Route::resource('rent-payment-reports', Admin\RentPaymentReportController::class)->only('index', 'show');
Route::post('rent-payment-reports/filter', [Admin\RentPaymentReportController::class, 'filter'])->name('rent-payment-reports.filter');
Route::get('rent-payment-reports-excel', [Admin\RentPaymentReportController::class, 'exportExcel'])->name('rent-payment-reports.excel');
Route::get('rent-payment-reports-csv', [Admin\RentPaymentReportController::class, 'exportCsv'])->name('rent-payment-reports.csv');

Route::resource('security-deposit-reports', Admin\SecurityDepositReportController::class)->only('index', 'show');
Route::post('security-deposit-reports/filter', [Admin\SecurityDepositReportController::class, 'filter'])->name('security-deposit-reports.filter');
Route::get('security-deposit-reports-excel', [Admin\SecurityDepositReportController::class, 'exportExcel'])->name('security-deposit-reports.excel');
Route::get('security-deposit-reports-csv', [Admin\SecurityDepositReportController::class, 'exportCsv'])->name('security-deposit-reports.csv');

Route::resource('subscription-reports', Admin\SubscriptionReportController::class)->only('index');
Route::post('subscription-reports/filter', [Admin\SubscriptionReportController::class, 'filter'])->name('subscription-reports.filter');
Route::get('subscription-reports-excel', [Admin\SubscriptionReportController::class, 'exportExcel'])->name('subscription-reports.excel');
Route::get('subscription-reports-csv', [Admin\SubscriptionReportController::class, 'exportCsv'])->name('subscription-reports.csv');

Route::resource('transaction-reports', Admin\TransactionReportController::class)->only('index');
Route::post('transaction-reports/filter', [Admin\TransactionReportController::class, 'filter'])->name('transaction-reports.filter');
Route::get('transaction-reports-excel', [Admin\TransactionReportController::class, 'exportExcel'])->name('transaction-reports.excel');
Route::get('transaction-reports-csv', [Admin\TransactionReportController::class, 'exportCsv'])->name('transaction-reports.csv');

Route::resource('supports', Admin\SupportController::class)->only('index');
Route::get('supports/get-details-msg/{id}', [Admin\SupportController::class, 'getDetailsMsg'])->name('supports.getDetailsMsg');
Route::post('supports/reply', [Admin\SupportController::class, 'reply'])->name('supports.reply');
Route::post('supports/filter', [Admin\SupportController::class, 'filter'])->name('supports.filter');

// Income Category
Route::resource('income-categories', Admin\IncomeCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('income-categories/filter', [Admin\IncomeCategoryController::class, 'filter'])->name('income-categories.filter');
Route::post('income-categories/delete-all', [Admin\IncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');
Route::post('income-categories/status/{id}', [Admin\IncomeCategoryController::class, 'status'])->name('income-categories.status');

// Income
Route::resource('incomes', Admin\IncomeController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('incomes/filter', [Admin\IncomeController::class, 'filter'])->name('incomes.filter');
Route::post('incomes/delete-all', [Admin\IncomeController::class, 'deleteAll'])->name('incomes.delete-all');
Route::get('incomes-excel', [Admin\IncomeController::class, 'exportExcel'])->name('incomes.excel');
Route::get('incomes-csv', [Admin\IncomeController::class, 'exportCsv'])->name('incomes.csv');

// Expenses Category
Route::resource('expense-categories', Admin\ExpenseCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('expense-categories/filter', [Admin\ExpenseCategoryController::class, 'filter'])->name('expense-categories.filter');
Route::post('expense-categories/delete-all', [Admin\ExpenseCategoryController::class, 'deleteAll'])->name('expense-categories.delete-all');
Route::post('expense-categories/status/{id}', [Admin\ExpenseCategoryController::class, 'status'])->name('expense-categories.status');

// Expenses
Route::resource('expenses', Admin\ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('expenses/filter', [Admin\ExpenseController::class, 'filter'])->name('expenses.filter');
Route::post('expenses/delete-all', [Admin\ExpenseController::class, 'deleteAll'])->name('expenses.delete-all');
Route::get('expenses-excel', [Admin\ExpenseController::class, 'exportExcel'])->name('expenses.excel');
Route::get('expenses-csv', [Admin\ExpenseController::class, 'exportCsv'])->name('expenses.csv');

// Message
Route::resource('messages', Admin\MessageController::class)->only('index', 'destroy');
Route::post('messages/filter', [Admin\MessageController::class, 'filter'])->name('messages.filter');
Route::post('messages/delete-all', [Admin\MessageController::class, 'deleteAll'])->name('messages.delete-all');

// Front CMS
Route::resource('website-settings', Admin\WebSettingController::class)->only('index', 'update');

// Term And Condition Controller
Route::resource('term-conditions', Admin\TermConditionController::class)->only('index', 'store');

// Term And Condition Controller
Route::resource('privacy-policy', Admin\PrivacyPolicyController::class)->only('index', 'store');

Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
Route::post('testimonials/filter', [Admin\TestimonialController::class, 'filter'])->name('testimonials.filter');
Route::post('testimonials/delete-all', [Admin\TestimonialController::class, 'deleteAll'])->name('testimonials.delete-all');

Route::resource('features', Admin\FeatureController::class)->except('show');
Route::post('features/filter', [Admin\FeatureController::class, 'filter'])->name('features.filter');
Route::post('features/status/{id}', [Admin\FeatureController::class, 'status'])->name('features.status');
Route::post('features/delete-all', [Admin\FeatureController::class, 'deleteAll'])->name('features.delete-all');

Route::resource('clients', Admin\ClientController::class)->except('show');
Route::post('clients/filter', [Admin\ClientController::class, 'filter'])->name('clients.filter');
Route::post('clients/status/{id}', [Admin\ClientController::class, 'status'])->name('clients.status');
Route::post('clients/delete-all', [Admin\ClientController::class, 'deleteAll'])->name('clients.delete-all');

Route::resource('blogs', Admin\BlogController::class)->except('show');
Route::post('blogs/filter', [Admin\BlogController::class, 'filter'])->name('blogs.filter');
Route::post('blogs/status/{id}', [Admin\BlogController::class, 'status'])->name('blogs.status');
Route::post('blogs/delete-all', [Admin\BlogController::class, 'deleteAll'])->name('blogs.delete-all');
Route::get('blogs/comments/{id}', [Admin\BlogController::class, 'filterComment'])->name('blogs.filter.comment');

Route::resource('highlights', Admin\HighlightController::class)->except('show');
Route::post('highlights/filter', [Admin\HighlightController::class, 'filter'])->name('highlights.filter');
Route::post('highlights/status/{id}', [Admin\HighlightController::class, 'status'])->name('highlights.status');
Route::post('highlights/delete-all', [Admin\HighlightController::class, 'deleteAll'])->name('highlights.delete-all');

Route::resource('comments', Admin\CommentController::class)->only('destroy');
Route::post('comments/delete-all', [Admin\CommentController::class, 'deleteAll'])->name('comments.delete-all');
Route::post('comments/filter', [Admin\CommentController::class, 'filter'])->name('comments.filter');

Route::resource('faqs', Admin\FaqController::class)->except('show');
Route::post('faqs/delete-all', [Admin\FaqController::class, 'deleteAll'])->name('faqs.delete-all');
Route::post('faqs/filter', [Admin\FaqController::class, 'filter'])->name('faqs.filter');
Route::post('faqs/status/{id}', [Admin\FaqController::class, 'status'])->name('faqs.status');

Route::resource('manage-settings', Admin\SettingsManagerController::class)->only('index', 'store');

Route::resource('languages', Admin\LanguageController::class)->except('edit', 'create');
Route::post('languages/filter', [Admin\LanguageController::class, 'filter'])->name('languages.filter');
Route::post('languages/delete-all', [Admin\LanguageController::class, 'deleteAll'])->name('languages.delete-all');
Route::post('languages/status/{id}', [Admin\LanguageController::class, 'status'])->name('languages.status');
Route::post('languages/default/{id}', [Admin\LanguageController::class, 'isDefault'])->name('languages.default');

Route::resource('translations', Admin\TranslationController::class)->only('store', 'update', 'destroy');
Route::post('translations/bulk-update/{lang}', [Admin\TranslationController::class, 'bulkUpdate'])->name('translations.bulk-update');

// Notifications manager
Route::prefix('notifications')->controller(Admin\NotificationController::class)->name('notifications.')->group(function () {
    Route::get('/', 'mtIndex')->name('index');
    Route::get('/{id}', 'mtView')->name('mtView');
    Route::get('view/all', 'mtReadAll')->name('mtReadAll');
    Route::post('filter', 'filter')->name('filter');
});
