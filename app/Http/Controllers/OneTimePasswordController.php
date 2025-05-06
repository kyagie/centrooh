<?php

namespace App\Http\Controllers;

use App\Models\OneTimePassword;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OneTimePasswordController extends Controller
{
    /**
     * Generate and send OTP to a phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^(\+?256|0)7[0-9]{8}$/'
        ], [
            'phone_number.regex' => 'Please provide a valid Ugandan phone number.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->input('phone_number');
        
        // Create OTP record
        $otp = OneTimePassword::createForPhone($phoneNumber);
        
        // In a real application, we would send SMS here
        // For now, we'll log the OTP for testing purposes
        Log::info("OTP for {$phoneNumber}: {$otp->otp_code}");
        
        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully to your phone number',
            'data' => [
                'phone_number' => $phoneNumber,
                // Only send OTP in response for development
                'otp' => config('app.env') === 'local' ? $otp->otp_code : null
            ]
        ]);
    }

    /**
     * Verify an OTP from a phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^(\+?256|0)7[0-9]{8}$/',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->input('phone_number');
        $otpCode = $request->input('otp_code');
        
        // Verify the OTP
        $isValid = OneTimePassword::verifyOtp($phoneNumber, $otpCode);
        
        if (!$isValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired OTP code'
            ], 400);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully',
            'data' => [
                'phone_number' => $phoneNumber,
                'verified' => true
            ]
        ]);
    }

    /**
     * Resend an OTP to a phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^(\+?256|0)7[0-9]{8}$/'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->input('phone_number');
        
        // Create a new OTP (this will override any existing one)
        $otp = OneTimePassword::createForPhone($phoneNumber);
        
        // In a real application, we would send SMS here
        // For now, we'll log the OTP for testing purposes
        Log::info("Resent OTP for {$phoneNumber}: {$otp->otp_code}");
        
        return response()->json([
            'status' => 'success',
            'message' => 'OTP resent successfully to your phone number',
            'data' => [
                'phone_number' => $phoneNumber,
                // Only send OTP in response for development
                'otp' => config('app.env') === 'local' ? $otp->otp_code : null
            ]
        ]);
    }

    /**
     * Check if a phone number has a verified OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkVerificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^(\+?256|0)7[0-9]{8}$/'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->input('phone_number');
        
        // Normalize the phone number
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Check if there's a verified OTP
        $otp = OneTimePassword::where('phone_number', $normalizedPhone)
                               ->where('verified', true)
                               ->where('expires_at', '>', now())
                               ->first();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'phone_number' => $phoneNumber,
                'verified' => (bool) $otp
            ]
        ]);
    }
}
