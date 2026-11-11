<?php

use App\Http\Controllers as Web;
use Illuminate\Support\Facades\Route;

Route::get('/', [Web\WebController::class, 'index'])->name('home');
Route::resource('blogs', Web\BlogController::class)->only('index', 'show', 'store');
Route::resource('highlights', Web\HighlightController::class)->only('show');
Route::get('/filter-blogs-by-tag', [Web\BlogController::class, 'filterBlogsByTag'])->name('blogs.tag.filter');
Route::get('/plans', [Web\PlanController::class, 'index'])->name('plan.index');
Route::get('/about-us', [Web\AboutController::class, 'index'])->name('about.index');
Route::get('/terms-conditions', [Web\TermServiceController::class, 'index'])->name('term.index');
Route::get('/privacy-policy', [Web\PolicyController::class, 'index'])->name('policy.index');

Route::get('/contact-us', [Web\ContactController::class, 'index'])->name('contact.index');
Route::post('/contact/store', [Web\ContactController::class, 'store'])->name('contact.store');

// Security Deposit & Rent payment
Route::get('/online-payments-gateways/{payment_id}/{tenant_id}', [Web\PaymentController::class, 'DepositRentPayment'])->name('online.payments-gateways.index');

// Subscription Payment
Route::get('/payments-gateways/{plan_id}/{landlord_id}', [Web\PaymentController::class, 'index'])->name('payments-gateways.index');

Route::post('/payments/{plan_id}/{gateway_id}', [Web\PaymentController::class, 'payment'])->name('payments-gateways.payment');
Route::get('/payment/success', [Web\PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [Web\PaymentController::class, 'failed'])->name('payment.failed');
Route::get('/order-status', [Web\PaymentController::class, 'orderStatus'])->name('order.status');
// Payment Routes End

Route::group([
    'namespace' => 'App\Library',
], function () {
    Route::get('/payment/mollie', 'Mollie@status');
    Route::post('/payment/paystack', 'Paystack@status')->name('paystack.status');
    Route::get('/paystack', 'Paystack@view')->name('paystack.view');
    Route::get('/razorpay/payment', 'Razorpay@view')->name('razorpay.view');
    Route::post('/razorpay/status', 'Razorpay@status');
    Route::get('/payment/flutterwave', 'Flutterwave@status');
    Route::get('/payment/thawani', 'Thawani@status');
    Route::get('/payment/instamojo', 'Instamojo@status');
    Route::get('/payment/toyyibpay', 'Toyyibpay@status');
    Route::get('/payment/billplz', 'Billplz@status');
    Route::get('/tap-payment/status', 'TapPayment@status')->name('tap-payment.status');
    Route::get('/fedapay/status', 'FedaPayGateway@status')->name('fedapay.status');
});

require __DIR__.'/maintenance.php';
