<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-otp', [AuthController::class, 'registerSendOtp'])->name('register.send-otp');
    Route::get('/register/verify', [AuthController::class, 'showVerifyOtpForm'])->name('auth.verify-otp-form');
    Route::post('/register/verify', [AuthController::class, 'registerVerifyOtp'])->name('register.verify-otp');

    Route::get('/password/reset', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/password/reset/send-otp', [AuthController::class, 'resetPasswordSendOtp'])->name('password.send-otp');
    Route::get('/password/reset/verify', [AuthController::class, 'showResetPasswordForm'])->name('password.reset-form');
    Route::post('/password/reset/verify', [AuthController::class, 'resetPasswordVerify'])->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});