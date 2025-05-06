<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OneTimePasswordController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\DeviceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Agent registration
Route::post('/agents/register', [AgentController::class, 'register']);

// Device routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('devices', DeviceController::class);
    Route::post('devices/{device}/regenerate-token', [DeviceController::class, 'regenerateToken']);
    Route::get('agents/{agent}/devices', [DeviceController::class, 'getAgentDevices']);
});

// OTP routes (no auth required for these endpoints)
Route::post('/otp/generate', [OneTimePasswordController::class, 'generateOtp']);
Route::post('/otp/verify', [OneTimePasswordController::class, 'verifyOtp']);
Route::post('/otp/resend', [OneTimePasswordController::class, 'resendOtp']);
Route::post('/otp/check-status', [OneTimePasswordController::class, 'checkVerificationStatus']);
