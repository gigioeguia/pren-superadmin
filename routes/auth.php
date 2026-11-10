<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;

Route::controller(Auth\LoginController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/login/store', 'login');
});

Route::controller(Auth\SignupController::class)->group(function () {
    Route::post('/signup', 'store')->name('signup.store');
    Route::post('/otp-submit', 'submitOtp')->name('signup.submitOtp');
    Route::post('/otp-resend', 'otpResend')->name('signup.otpResend');
});

Route::controller(Auth\PasswordResetLinkController::class)->group(function () {
    Route::get('/forgot-password', 'create')->name('password.request');
    Route::post('/forgot-password', 'store')->name('password.email');
});

Route::controller(Auth\NewPasswordController::class)->group(function () {
    Route::get('/reset-password/{token}', 'create')->name('password.reset');
    Route::post('/reset-password', 'store')->name('password.store');
});

Route::get('/logout', [Auth\LoginController::class, 'logout'])->name('logout');
