<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\ConversationController;
use App\Http\Controllers\Front\StoryController;
use App\Http\Controllers\Front\ProfileController;
use App\Http\Controllers\Front\SubscriptionController;
use App\Http\Controllers\Front\MessageController;
use App\Http\Controllers\Front\NotificationController;
use App\Http\Controllers\Front\ContactController;
use Illuminate\Support\Facades\Cookie;

Route::match(['get', 'post'], '/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'tk', 'ru'])) {
        session()->put('locale', $locale);
        cookie()->queue(cookie()->forever('locale', $locale));
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
    Route::post('/stories', [StoryController::class, 'store'])->name('stories.store');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{plan}/pay', [SubscriptionController::class, 'payWithCrypto'])->name('subscriptions.pay');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/privacy', [ProfileController::class, 'updatePrivacy'])->name('profile.privacy.update');

    Route::post('/conversations/create', [ConversationController::class, 'store'])->name('conversations.store');

    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::post('/messages/{message}/react', [MessageController::class, 'react'])->name('messages.react');
    Route::post('/conversations/{conversation}/gift', [MessageController::class, 'sendGift'])->name('conversations.gift');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
});