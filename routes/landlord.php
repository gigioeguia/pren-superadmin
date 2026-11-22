<?php

use App\Http\Controllers\Landlord;
use Illuminate\Support\Facades\Route;

// profile route ...
Route::resource('profile', Landlord\ProfileController::class)->only('index', 'update');

Route::get('/', [Landlord\DashboardController::class, 'index'])->name('dashboard');
Route::get('/getDashboardData', [Landlord\DashboardController::class, 'getDashboardData'])->name('getDashboardData');
Route::get('/property-chart', [Landlord\DashboardController::class, 'property_chart'])->name('getPropertyData');
Route::get('/account-chart', [Landlord\DashboardController::class, 'account_chart'])->name('getAccountData');

Route::resource('properties', Landlord\PropertyController::class);
Route::post('properties/filter', [Landlord\PropertyController::class, 'filter'])->name('properties.filter');
Route::post('properties/delete-all', [Landlord\PropertyController::class, 'deleteAll'])->name('properties.delete-all');
Route::get('properties/success/{id}', [Landlord\PropertyController::class, 'successView'])->name('properties.success');
Route::post('properties/status/{id}', [Landlord\PropertyController::class, 'status'])->name('properties.status');
Route::get('properties-excel', [Landlord\PropertyController::class, 'exportExcel'])->name('properties.excel');
Route::get('properties-csv', [Landlord\PropertyController::class, 'exportCsv'])->name('properties.csv');

Route::resource('tenants', Landlord\TenantController::class);
Route::post('tenants/filter', [Landlord\TenantController::class, 'filter'])->name('tenants.filter');
Route::post('tenants/delete-all', [Landlord\TenantController::class, 'deleteAll'])->name('tenants.delete-all');
Route::get('tenants-excel', [Landlord\TenantController::class, 'exportExcel'])->name('tenants.excel');
Route::get('tenants-csv', [Landlord\TenantController::class, 'exportCsv'])->name('tenants.csv');

Route::resource('maintenances', Landlord\MaintenanceController::class)->only('index', 'show');
Route::post('maintenances/filter', [Landlord\MaintenanceController::class, 'filter'])->name('maintenances.filter');
Route::post('maintenances/rejected/{id}', [Landlord\MaintenanceController::class, 'rejected'])->name('maintenances.rejected');
Route::post('maintenances/processing/{id}', [Landlord\MaintenanceController::class, 'processing'])->name('maintenances.processing');
Route::post('maintenance/completed/{id}', [Landlord\MaintenanceController::class, 'completed'])->name('maintenances.completed');
Route::get('maintenances-excel', [Landlord\MaintenanceController::class, 'exportExcel'])->name('maintenances.excel');
Route::get('maintenances-csv', [Landlord\MaintenanceController::class, 'exportCsv'])->name('maintenances.csv');

Route::resource('maintenance-reports', Landlord\MaintenanceReportController::class)->only('index');
Route::post('maintenance-reports/filter', [Landlord\MaintenanceReportController::class, 'filter'])->name('maintenance-reports.filter');
Route::get('maintenance-reports-excel', [Landlord\MaintenanceReportController::class, 'exportExcel'])->name('maintenance-reports.excel');
Route::get('maintenance-reports-csv', [Landlord\MaintenanceReportController::class, 'exportCsv'])->name('maintenance-reports.csv');

Route::resource('labors', Landlord\LaborController::class);
Route::post('labors/filter', [Landlord\LaborController::class, 'filter'])->name('labors.filter');
Route::post('labors/delete-all', [Landlord\LaborController::class, 'deleteAll'])->name('labors.delete-all');
Route::get('labors-excel', [Landlord\LaborController::class, 'exportExcel'])->name('labors.excel');
Route::get('labors-csv', [Landlord\LaborController::class, 'exportCsv'])->name('labors.csv');

Route::resource('tenant-applications', Landlord\ApplicationController::class)->only('index', 'show');
Route::post('tenant-applications/filter', [Landlord\ApplicationController::class, 'filter'])->name('application.filter');
Route::post('application/processing/{id}', [Landlord\ApplicationController::class, 'processing'])->name('application.processing');
Route::post('application/approved/{id}', [Landlord\ApplicationController::class, 'approved'])->name('application.approved');
Route::post('application/rejected/{id}', [Landlord\ApplicationController::class, 'rejected'])->name('application.rejected');
Route::get('application/file/{id}/{type}', [Landlord\ApplicationController::class, 'file_download'])->name('application.file-download');
Route::get('applications-excel', [Landlord\ApplicationController::class, 'exportExcel'])->name('applications.excel');
Route::get('applications-csv', [Landlord\ApplicationController::class, 'exportCsv'])->name('applications.csv');

Route::resource('security-deposits', Landlord\SecurityDepositController::class)->only('index', 'show');
Route::post('security-deposits/filter', [Landlord\SecurityDepositController::class, 'filter'])->name('security-deposits.filter');
Route::get('security-deposits-excel', [Landlord\SecurityDepositController::class, 'exportExcel'])->name('security-deposits.excel');
Route::get('security-deposits-csv', [Landlord\SecurityDepositController::class, 'exportCsv'])->name('security-deposits.csv');

Route::resource('refund-request', Landlord\RefundRequestController::class)->only('index', 'show');
Route::post('refund-request/filter', [Landlord\RefundRequestController::class, 'filter'])->name('refund-request.filter');
Route::get('refund-request-excel', [Landlord\RefundRequestController::class, 'exportExcel'])->name('refund-request.excel');
Route::get('refund-request-csv', [Landlord\RefundRequestController::class, 'exportCsv'])->name('refund-request.csv');

Route::resource('rent-payments', Landlord\RentPaymentController::class)->only('index', 'show');
Route::post('rent-payments/filter', [Landlord\RentPaymentController::class, 'filter'])->name('rent-payments.filter');
Route::get('rent-payments-excel', [Landlord\RentPaymentController::class, 'exportExcel'])->name('rent-payments.excel');
Route::get('rent-payments-csv', [Landlord\RentPaymentController::class, 'exportCsv'])->name('rent-payments.csv');

Route::resource('rents', Landlord\RentInvitationController::class);
Route::post('rents/approved/{id}', [Landlord\RentInvitationController::class, 'approved'])->name('rents.approved');
Route::post('rents/canceled/{id}', [Landlord\RentInvitationController::class, 'canceled'])->name('rents.canceled');
Route::post('rents/filter', [Landlord\RentInvitationController::class, 'filter'])->name('rents.filter');
Route::get('rents-excel', [Landlord\RentInvitationController::class, 'exportExcel'])->name('rents.excel');
Route::get('rents-csv', [Landlord\RentInvitationController::class, 'exportCsv'])->name('rents.csv');
Route::get('get-units', [Landlord\RentInvitationController::class, 'getUnit'])->name('get.units');

Route::resource('withdraw-method', Landlord\WithdrawMethodController::class);
Route::post('withdraw-method/filter', [Landlord\WithdrawMethodController::class, 'filter'])->name('withdraw-method.filter');
Route::post('withdraw-method/delete-all', [Landlord\WithdrawMethodController::class, 'deleteAll'])->name('withdraw-method.delete-all');
Route::get('withdraw-method-excel', [Landlord\WithdrawMethodController::class, 'exportExcel'])->name('withdraw-method.excel');
Route::get('withdraw-method-csv', [Landlord\WithdrawMethodController::class, 'exportCsv'])->name('withdraw-method.csv');

Route::resource('withdraw', Landlord\WithdrawController::class);
Route::post('withdraw/filter', [Landlord\WithdrawController::class, 'filter'])->name('withdraw.filter');
Route::get('withdraw-excel', [Landlord\WithdrawController::class, 'exportExcel'])->name('withdraw.excel');
Route::get('withdraw-csv', [Landlord\WithdrawController::class, 'exportCsv'])->name('withdraw.csv');

Route::resource('supports', Landlord\SupportController::class)->only('index', 'store');
Route::get('support/get-message', [Landlord\SupportController::class, 'getMessage'])->name('supports.get-message');
Route::post('supports/filter', [Landlord\SupportController::class, 'filter'])->name('supports.filter');

// Income Category
Route::resource('income-categories', Landlord\IncomeCategoryController::class);
Route::post('income-categories/filter', [Landlord\IncomeCategoryController::class, 'filter'])->name('income-categories.filter');
Route::post('income-categories/delete-all', [Landlord\IncomeCategoryController::class, 'deleteAll'])->name('income-categories.delete-all');
Route::post('income-categories/status/{id}', [Landlord\IncomeCategoryController::class, 'status'])->name('income-categories.status');

// Income
Route::resource('incomes', Landlord\IncomeController::class);
Route::post('incomes/filter', [Landlord\IncomeController::class, 'filter'])->name('incomes.filter');
Route::post('incomes/delete-all', [Landlord\IncomeController::class, 'deleteAll'])->name('incomes.delete-all');
Route::get('incomes-excel', [Landlord\IncomeController::class, 'exportExcel'])->name('incomes.excel');
Route::get('incomes-csv', [Landlord\IncomeController::class, 'exportCsv'])->name('incomes.csv');
Route::post('incomes/status/{id}', [Landlord\IncomeController::class, 'status'])->name('incomes.status');

// Expenses Category
Route::resource('expense-categories', Landlord\ExpenseCategoryController::class);
Route::post('expense-categories/filter', [Landlord\ExpenseCategoryController::class, 'filter'])->name('expense-categories.filter');
Route::post('expense-categories/delete-all', [Landlord\ExpenseCategoryController::class, 'deleteAll'])->name('expense-categories.delete-all');
Route::post('expense-categories/status/{id}', [Landlord\ExpenseCategoryController::class, 'status'])->name('expense-categories.status');

// Expenses
Route::resource('expenses', Landlord\ExpenseController::class);
Route::post('expenses/filter', [Landlord\ExpenseController::class, 'filter'])->name('expenses.filter');
Route::post('expenses/delete-all', [Landlord\ExpenseController::class, 'deleteAll'])->name('expenses.delete-all');
Route::get('expenses-excel', [Landlord\ExpenseController::class, 'exportExcel'])->name('expenses.excel');
Route::get('expenses-csv', [Landlord\ExpenseController::class, 'exportCsv'])->name('expenses.csv');
Route::post('expenses/status/{id}', [Landlord\ExpenseController::class, 'status'])->name('expenses.status');

// Facility
Route::resource('facilities', Landlord\FacilityController::class);
Route::post('facilities/filter', [Landlord\FacilityController::class, 'filter'])->name('facilities.filter');
Route::post('facilities/delete-all', [Landlord\FacilityController::class, 'deleteAll'])->name('facilities.delete-all');
Route::post('facilities/status/{id}', [Landlord\FacilityController::class, 'status'])->name('facilities.status');

// Amenity
Route::resource('amenities', Landlord\AmenityController::class);
Route::post('amenities/filter', [Landlord\AmenityController::class, 'filter'])->name('amenities.filter');
Route::post('amenities/delete-all', [Landlord\AmenityController::class, 'deleteAll'])->name('amenities.delete-all');
Route::post('amenities/status/{id}', [Landlord\AmenityController::class, 'status'])->name('amenities.status');

Route::resource('subscriptions', Landlord\SubscriptionController::class)->withoutMiddleware('expired')->only('index');

// Notifications manager
Route::prefix('notifications')->controller(Landlord\NotificationController::class)->name('notifications.')->group(function () {
    Route::get('/', 'mtIndex')->name('index');
    Route::get('/{id}', 'mtView')->name('mtView');
    Route::get('view/all', 'mtReadAll')->name('mtReadAll');
    Route::post('filter', 'filter')->name('filter');
});
