<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FaqController;

Route::prefix('auth')->group(function () {
    Route::post('/register/send-otp', [AuthController::class, 'registerSendOtp']);
    Route::post('/register/verify-otp', [AuthController::class, 'registerVerifyOtp']);
    
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    
    Route::post('/password/reset-send-otp', [AuthController::class, 'resetPasswordSendOtp']);
    Route::post('/password/reset-verify', [AuthController::class, 'resetPasswordVerify']);
});

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/user/profile', [UserController::class, 'update']);
    Route::post('/user/privacy', [UserController::class, 'updatePrivacy']);

    Route::apiResource('conversations', ConversationController::class)->only(['index', 'store', 'show']);
    
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    Route::get('/stories', [StoryController::class, 'index']);
    Route::post('/stories', [StoryController::class, 'store']);
    Route::post('/stories/{story}/view', [StoryController::class, 'view']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    
    Route::get('/faqs', [FaqController::class, 'index']);
    
    Route::get('/stars/balance', [UserController::class, 'getStarBalance']);
});