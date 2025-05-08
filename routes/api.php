<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OneTimePasswordController;
use App\Http\Controllers\AgentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Agent registration
Route::post('/agents/register', [AgentController::class, 'register']);
Route::post('/otp/generate', [OneTimePasswordController::class, 'generateOtp']);
Route::post('/otp/verify', [OneTimePasswordController::class, 'verifyOtp']);
Route::post('/otp/resend', [OneTimePasswordController::class, 'resendOtp']);
Route::post('/otp/check-status', [OneTimePasswordController::class, 'checkVerificationStatus']);
