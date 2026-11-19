<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::controller(Auth\LoginController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/login/store', 'login')->middleware('throttle:5,1');
});

Route::controller(Auth\SignupController::class)->group(function () {
    Route::post('/signup', 'store')->name('signup.store')->middleware('throttle:5,1');
    Route::post('/otp-submit', 'submitOtp')->name('signup.submitOtp')->middleware('throttle:5,1');
    Route::post('/otp-resend', 'otpResend')->name('signup.otpResend')->middleware('throttle:5,1');
});

Route::controller(Auth\PasswordResetLinkController::class)->group(function () {
    Route::get('/forgot-password', 'create')->name('password.request');
    Route::post('/forgot-password', 'store')->name('password.email')->middleware('throttle:5,1');
});

Route::controller(Auth\NewPasswordController::class)->group(function () {
    Route::get('/reset-password/{token}', 'create')->name('password.reset');
    Route::post('/reset-password', 'store')->name('password.store')->middleware('throttle:5,1');
});

Route::get('/logout', [Auth\LoginController::class, 'logout'])->name('logout');
