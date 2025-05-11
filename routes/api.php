<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AgentController, AuthController};
use App\Http\Middleware\EnsureUserIsAgent;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Agent registration.
Route::post('/agent/register', [AgentController::class, 'register']);

// OTP routes with rate limiting
Route::middleware('throttle:otp')->group(function () {
    // Authentication with OTP
    Route::post('/auth/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/agent/me', [AuthController::class, 'me']);
    Route::post('/agent/logout', [AuthController::class, 'logout']);
    Route::post('/agent/logout-all', [AuthController::class, 'logoutAll']);

    // Agent-specific routes (requires user to have an agent profile)
    Route::middleware([EnsureUserIsAgent::class])->group(function () {
        Route::get('/agent/billboards', [AgentController::class, 'getAssignedBillboards']);
        Route::post('/agent/billboards/upload-image', [AgentController::class, 'uploadBillboardImage']);
    });
});
