<?php

use App\Http\Controllers\Landlord;
use Illuminate\Support\Facades\Route;

// profile route ...
Route::resource('profile', Landlord\AcnooProfileController::class)->only('index', 'update');

Route::get('/', [Landlord\DashboardController::class, 'index'])->name('dashboard');
Route::get('/getDashboardData', [Landlord\DashboardController::class, 'getDashboardData'])->name('getDashboardData');
Route::get('/property-chart', [Landlord\DashboardController::class, 'property_chart'])->name('getPropertyData');
Route::get('/account-chart', [Landlord\DashboardController::class, 'account_chart'])->name('getAccountData');

Route::resource('properties', Landlord\PropertyController::class);
Route::post('properties/filter', [Landlord\PropertyController::class, 'deleteAll'])->name('properties.delete-all');
Route::post('properties/delete-all', [Landlord\PropertyController::class, 'acnooFilter'])->name('properties.filter');
Route::get('properties/success/{id}', [Landlord\PropertyController::class, 'successView'])->name('properties.success');
Route::post('properties/status/{id}', [Landlord\PropertyController::class, 'status'])->name('properties.status');
Route::get('properties-excel', [Landlord\PropertyController::class, 'exportExcel'])->name('properties.excel');
Route::get('properties-csv', [Landlord\PropertyController::class, 'exportCsv'])->name('properties.csv');

Route::resource('tenants', Landlord\AcnooTenantController::class);
Route::post('tenants/filter', [Landlord\AcnooTenantController::class, 'acnooFilter'])->name('tenants.filter');
Route::post('tenants/delete-all', [Landlord\AcnooTenantController::class, 'deleteAll'])->name('tenants.delete-all');
Route::get('tenants-excel', [Landlord\AcnooTenantController::class, 'exportExcel'])->name('tenants.excel');
Route::get('tenants-csv', [Landlord\AcnooTenantController::class, 'exportCsv'])->name('tenants.csv');

Route::resource('maintenances', Landlord\AcnooMaintenanceController::class)->only('index', 'show');
Route::post('maintenances/filter', [Landlord\AcnooMaintenanceController::class, 'acnooFilter'])->name('maintenances.filter');
Route::post('maintenances/rejected/{id}', [Landlord\AcnooMaintenanceController::class, 'rejected'])->name('maintenances.rejected');
Route::post('maintenances/processing/{id}', [Landlord\AcnooMaintenanceController::class, 'processing'])->name('maintenances.processing');
Route::post('maintenance/completed/{id}', [Landlord\AcnooMaintenanceController::class, 'completed'])->name('maintenances.completed');
Route::get('maintenances-excel', [Landlord\AcnooMaintenanceController::class, 'exportExcel'])->name('maintenances.excel');
Route::get('maintenances-csv', [Landlord\AcnooMaintenanceController::class, 'exportCsv'])->name('maintenances.csv');

Route::resource('maintenance-reports', Landlord\AcnooMaintenanceReportController::class)->only('index');
Route::post('maintenance-reports/filter', [Landlord\AcnooMaintenanceReportController::class, 'acnooFilter'])->name('maintenance-reports.filter');
Route::get('maintenance-reports-excel', [Landlord\AcnooMaintenanceReportController::class, 'exportExcel'])->name('maintenance-reports.excel');
Route::get('maintenance-reports-csv', [Landlord\AcnooMaintenanceReportController::class, 'exportCsv'])->name('maintenance-reports.csv');

Route::resource('labors', Landlord\AcnooLaborController::class);
Route::post('labors/filter', [Landlord\AcnooLaborController::class, 'acnooFilter'])->name('labors.filter');
Route::post('labors/delete-all', [Landlord\AcnooLaborController::class, 'deleteAll'])->name('labors.delete-all');
Route::get('labors-excel', [Landlord\AcnooLaborController::class, 'exportExcel'])->name('labors.excel');
Route::get('labors-csv', [Landlord\AcnooLaborController::class, 'exportCsv'])->name('labors.csv');

Route::resource('tenant-applications', Landlord\AcnooApplicationController::class)->only('index', 'show');
Route::post('tenant-applications/filter', [Landlord\AcnooApplicationController::class, 'acnooFilter'])->name('application.filter');
Route::post('application/processing/{id}', [Landlord\AcnooApplicationController::class, 'processing'])->name('application.processing');
Route::post('application/approved/{id}', [Landlord\AcnooApplicationController::class, 'approved'])->name('application.approved');
Route::post('application/rejected/{id}', [Landlord\AcnooApplicationController::class, 'rejected'])->name('application.rejected');
Route::get('application/file/{id}/{type}', [Landlord\AcnooApplicationController::class, 'file_download'])->name('application.file-download');
Route::get('applications-excel', [Landlord\AcnooApplicationController::class, 'exportExcel'])->name('applications.excel');
Route::get('applications-csv', [Landlord\AcnooApplicationController::class, 'exportCsv'])->name('applications.csv');

Route::resource('security-deposits', Landlord\AcnooSecurityDepositController::class)->only('index', 'show');
Route::post('security-deposits/filter', [Landlord\AcnooSecurityDepositController::class, 'acnooFilter'])->name('security-deposits.filter');
Route::get('security-deposits-excel', [Landlord\AcnooSecurityDepositController::class, 'exportExcel'])->name('security-deposits.excel');
Route::get('security-deposits-csv', [Landlord\AcnooSecurityDepositController::class, 'exportCsv'])->name('security-deposits.csv');

Route::resource('refund-request', Landlord\AcnooRefundRequestController::class)->only('index', 'show');
Route::post('refund-request/filter', [Landlord\AcnooRefundRequestController::class, 'acnooFilter'])->name('refund-request.filter');
Route::get('refund-request-excel', [Landlord\AcnooRefundRequestController::class, 'exportExcel'])->name('refund-request.excel');
Route::get('refund-request-csv', [Landlord\AcnooRefundRequestController::class, 'exportCsv'])->name('refund-request.csv');

Route::resource('rent-payments', Landlord\AcnooRentPaymentController::class)->only('index', 'show');
Route::post('rent-payments/filter', [Landlord\AcnooRentPaymentController::class, 'acnooFilter'])->name('rent-payments.filter');
Route::get('rent-payments-excel', [Landlord\AcnooRentPaymentController::class, 'exportExcel'])->name('rent-payments.excel');
Route::get('rent-payments-csv', [Landlord\AcnooRentPaymentController::class, 'exportCsv'])->name('rent-payments.csv');

Route::resource('rents', Landlord\AcnooRentInvitationController::class);
Route::post('rents/approved/{id}', [Landlord\AcnooRentInvitationController::class, 'approved'])->name('rents.approved');
Route::post('rents/canceled/{id}', [Landlord\AcnooRentInvitationController::class, 'canceled'])->name('rents.canceled');
Route::post('rents/filter', [Landlord\AcnooRentInvitationController::class, 'acnooFilter'])->name('rents.filter');
Route::get('rents-excel', [Landlord\AcnooRentInvitationController::class, 'exportExcel'])->name('rents.excel');
Route::get('rents-csv', [Landlord\AcnooRentInvitationController::class, 'exportCsv'])->name('rents.csv');
Route::get('get-units', [Landlord\AcnooRentInvitationController::class, 'getUnit'])->name('get.units');

Route::resource('withdraw-method', Landlord\AcnooWithdrawMethodController::class);
Route::post('withdraw-method/filter', [Landlord\AcnooWithdrawMethodController::class, 'acnooFilter'])->name('withdraw-method.filter');
Route::post('withdraw-method/delete-all', [Landlord\AcnooWithdrawMethodController::class, 'deleteAll'])->name('withdraw-method.delete-all');
Route::get('withdraw-method-excel', [Landlord\AcnooWithdrawMethodController::class, 'exportExcel'])->name('withdraw-method.excel');
Route::get('withdraw-method-csv', [Landlord\AcnooWithdrawMethodController::class, 'exportCsv'])->name('withdraw-method.csv');

Route::resource('withdraw', Landlord\AcnooWithdrawController::class);
Route::post('withdraw/filter', [Landlord\AcnooWithdrawController::class, 'acnooFilter'])->name('withdraw.filter');
Route::get('withdraw-excel', [Landlord\AcnooWithdrawController::class, 'exportExcel'])->name('withdraw.excel');
Route::get('withdraw-csv', [Landlord\AcnooWithdrawController::class, 'exportCsv'])->name('withdraw.csv');

Route::resource('supports', Landlord\AcnooSupportController::class)->only('index', 'store');
Route::get('support/get-message', [Landlord\AcnooSupportController::class, 'getMessage'])->name('supports.get-message');
Route::post('supports/filter', [Landlord\AcnooSupportController::class, 'acnooFilter'])->name('supports.filter');

// Income Category
Route::resource('income-categories', Landlord\AcnooIncomeCategoryController::class);
Route::post('income-categories/filter', [Landlord\AcnooIncomeCategoryController::class, 'acnooFilter'])->name('income-categories.filter');
Route::post('income-categories/delete-all', [Landlord\AcnooIncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');
Route::post('income-categories/status/{id}', [Landlord\AcnooIncomeCategoryController::class, 'status'])->name('income-categories.status');

// Income
Route::resource('incomes', Landlord\AcnooIncomeController::class);
Route::post('incomes/filter', [Landlord\AcnooIncomeController::class, 'acnooFilter'])->name('incomes.filter');
Route::post('incomes/delete-all', [Landlord\AcnooIncomeController::class, 'deleteAll'])->name('incomes.delete-all');
Route::get('incomes-excel', [Landlord\AcnooIncomeController::class, 'exportExcel'])->name('incomes.excel');
Route::get('incomes-csv', [Landlord\AcnooIncomeController::class, 'exportCsv'])->name('incomes.csv');
Route::post('incomes/status/{id}', [Landlord\AcnooIncomeController::class, 'status'])->name('incomes.status');

// Expenses Category
Route::resource('expense-categories', Landlord\AcnooExpenseCategoryController::class);
Route::post('expense-categories/filter', [Landlord\AcnooExpenseCategoryController::class, 'acnooFilter'])->name('expense-categories.filter');
Route::post('expense-categories/delete-all', [Landlord\AcnooExpenseCategoryController::class, 'deleteAll'])->name('expense-categories.delete-all');
Route::post('expense-categories/status/{id}', [Landlord\AcnooExpenseCategoryController::class, 'status'])->name('expense-categories.status');

// Expenses
Route::resource('expenses', Landlord\AcnooExpenseController::class);
Route::post('expenses/filter', [Landlord\AcnooExpenseController::class, 'acnooFilter'])->name('expenses.filter');
Route::post('expenses/delete-all', [Landlord\AcnooExpenseController::class, 'deleteAll'])->name('expenses.delete-all');
Route::get('expenses-excel', [Landlord\AcnooExpenseController::class, 'exportExcel'])->name('expenses.excel');
Route::get('expenses-csv', [Landlord\AcnooExpenseController::class, 'exportCsv'])->name('expenses.csv');
Route::post('expenses/status/{id}', [Landlord\AcnooExpenseController::class, 'status'])->name('expenses.status');

// Facility
Route::resource('facilities', Landlord\AcnooFacilityController::class);
Route::post('facilities/filter', [Landlord\AcnooFacilityController::class, 'acnooFilter'])->name('facilities.filter');
Route::post('facilities/delete-all', [Landlord\AcnooFacilityController::class, 'deleteAll'])->name('facilities.delete-all');
Route::post('facilities/status/{id}', [Landlord\AcnooFacilityController::class, 'status'])->name('facilities.status');

// Amenity
Route::resource('amenities', Landlord\AcnooAmenityController::class);
Route::post('amenities/filter', [Landlord\AcnooAmenityController::class, 'acnooFilter'])->name('amenities.filter');
Route::post('amenities/delete-all', [Landlord\AcnooAmenityController::class, 'deleteAll'])->name('amenities.delete-all');
Route::post('amenities/status/{id}', [Landlord\AcnooAmenityController::class, 'status'])->name('amenities.status');

Route::resource('subscriptions', Landlord\SubscriptionController::class)->withoutMiddleware('expired')->only('index');

// Notifications manager
Route::prefix('notifications')->controller(Landlord\NotificationController::class)->name('notifications.')->group(function () {
    Route::get('/', 'mtIndex')->name('index');
    Route::get('/{id}', 'mtView')->name('mtView');
    Route::get('view/all/', 'mtReadAll')->name('mtReadAll');
    Route::post('notification/filter/', 'acnooFilter')->name('filter');
});
