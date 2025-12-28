<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('ems')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone']);
    Route::post('/auth/password-reset/request', [AuthController::class, 'requestPasswordReset']);
    Route::post('/auth/password-reset/confirm', [AuthController::class, 'confirmPasswordReset']);

    // Protected routes: use middleware to populate auth_user from token
    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('/users/me', [AuthController::class, 'profile']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
