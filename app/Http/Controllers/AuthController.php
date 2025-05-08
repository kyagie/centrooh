<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\OneTimePassword;
use App\Models\User;
use App\Jobs\SendOneTimePassword as SendOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Request an OTP for authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        // Normalize the phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $request->phone_number);
        
        // Create or update OTP
        $otp = OneTimePassword::createForPhone($phoneNumber);
        
        // Dispatch job to send OTP
        SendOTP::dispatch($otp);
        
        return response()->json([
            'message' => 'OTP sent successfully',
            'phone_number' => $phoneNumber
        ]);
    }
    
    /**
     * Verify OTP and authenticate the agent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp_code' => 'required|string',
            'device_name' => 'required|string',
        ]);
        
        // Normalize the phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $request->phone_number);
        
        // Verify the OTP
        $verified = OneTimePassword::verifyOtp($phoneNumber, $request->otp_code);
        
        if (!$verified) {
            throw ValidationException::withMessages([
                'otp_code' => ['The OTP is invalid or has expired.'],
            ]);
        }
        
        // Check if phone number belongs to an agent
        $agent = Agent::where('phone_number', $phoneNumber)->first();
        
        if (!$agent) {
            return response()->json([
                'message' => 'Phone number is not associated with any agent',
                'status' => 'unregistered'
            ], 404);
        }
        
        // Get the user associated with the agent
        $user = $agent->user;
        
        // Create a token with full abilities for the agent
        $token = $user->createToken($request->device_name, ['*']);
        
        return response()->json([
            'message' => 'OTP verified successfully',
            'status' => 'authenticated',
            'agent' => $agent->load(['region', 'district']),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addMinutes(config('sanctum.expiration'))
        ]);
    }
    
    /**
     * Get the authenticated agent's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $agent = $user->agent;
        
        if (!$agent) {
            return response()->json([
                'message' => 'User is not associated with any agent'
            ], 404);
        }
        
        return response()->json([
            'agent' => $agent->load(['region', 'district']),
            'user' => $user
        ]);
    }
    
    /**
     * Logout the agent by revoking the token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    
    /**
     * Logout from all devices by revoking all tokens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();
        
        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ]);
    }
}
