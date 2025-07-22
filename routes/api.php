<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
        Route::post('reset-password', [PasswordResetController::class, 'verifyResetCode']);
        
        
        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    // User Routes
    Route::middleware('auth:api')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/search', [UserController::class, 'search']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
    });
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});