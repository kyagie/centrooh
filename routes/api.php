<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{OneTimePasswordController, AgentController, AuthController};

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Agent registration
Route::post('/agents/register', [AgentController::class, 'register']);

// OTP routes with rate limiting
Route::middleware('throttle:otp')->group(function () {
    // Authentication with OTP
    Route::post('/auth/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

    // Agent-specific routes (requires user to have an agent profile)
    Route::middleware('ensure.agent')->group(function () {
        Route::get('/agent/billboards', [AgentController::class, 'getAssignedBillboards']);
        Route::post('/agent/billboards/upload-image', [AgentController::class, 'uploadBillboardImage']);
    });
});
