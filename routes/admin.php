<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\CryptoPaymentController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\StoryController;
use App\Http\Controllers\Admin\OtpGatewayController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\FaqController;

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{user}', 'show')->name('show');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::patch('/{user}/role', 'updateRole')->name('updateRole');
        Route::patch('/{user}/block', 'toggleBlock')->name('toggleBlock');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    Route::controller(ConversationController::class)->prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{conversation}', 'show')->name('show');
        Route::delete('/{conversation}', 'destroy')->name('destroy');
        Route::delete('/message/{message}', 'destroyMessage')->name('message.destroy');
    });

    Route::controller(CryptoPaymentController::class)->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/{payment}/approve', 'approve')->name('approve');
        Route::patch('/{payment}/reject', 'reject')->name('reject');
    });

    Route::controller(PlanController::class)->prefix('plans')->name('plans.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{plan}/edit', 'edit')->name('edit');
        Route::put('/{plan}', 'update')->name('update');
    });

    Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'update')->name('update');
        Route::patch('/translation/{setting}', 'updateTranslation')->name('translation.update');
    });

    Route::controller(StoryController::class)->prefix('stories')->name('stories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::delete('/{story}', 'destroy')->name('destroy');
    });

    Route::controller(OtpGatewayController::class)->prefix('otp-gateway')->name('otp.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/{message}/retry', 'retry')->name('retry');
        Route::delete('/{message}', 'destroy')->name('destroy');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/send', 'send')->name('send');
    });

    Route::controller(FaqController::class)->prefix('faqs')->name('faqs.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{faq}', 'update')->name('update');
        Route::patch('/{faq}/toggle', 'toggle')->name('toggle');
        Route::delete('/{faq}', 'destroy')->name('destroy');
    });
});