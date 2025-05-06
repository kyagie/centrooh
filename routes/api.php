<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OneTimePasswordController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/otp/generate', [OneTimePasswordController::class, 'generateOtp']);
Route::post('/otp/verify', [OneTimePasswordController::class, 'verifyOtp']);
Route::post('/otp/resend', [OneTimePasswordController::class, 'resendOtp']);
Route::post('/otp/check-status', [OneTimePasswordController::class, 'checkVerificationStatus']);
