<?php

namespace App\Http\Controllers;

use App\Models\{
    User,
    Agent,
    Device,
    OneTimePassword,
    Billboard,
    BillboardImage
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Register a new agent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate request data
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|regex:/^(\+?256|0)7[0-9]{8}$/|unique:users,phone_number',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8',
            'device_info' => 'nullable|array',
            'device_info.device_id' => 'nullable|string',
            'device_info.brand' => 'nullable|string',
            'device_info.name' => 'nullable|string',
            'device_info.ipaddress' => 'nullable|ip',
            'device_info.token' => 'nullable|string',
        ]);

        // Check if phone number has been verified with OTP
        $phoneNumber = $request->input('phone_number');
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        $verifiedOtp = OneTimePassword::where('phone_number', $normalizedPhone)
            ->where('verified', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verifiedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number has not been verified. Please verify your phone number first.'
            ], 400);
        }

        // Generate a unique username based on first and last name
        $username = Agent::generateUniqueUsername(
            $request->input('first_name'),
            $request->input('last_name')
        );

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
                'email' => $request->input('email'),
                'phone_number' => $phoneNumber,
                'password' => Hash::make($request->input('password')),
            ]);

            // Assign agent role to user
            $agentRole = Role::firstOrCreate(['name' => 'agent']);
            $user->assignRole($agentRole);

            // Create agent
            $agent = Agent::create([
                'username' => $username,
                'phone_number' => $phoneNumber,
                'status' => 'pending',
                'user_id' => $user->id,
            ]);

            // Register device if device info is provided
            $deviceToken = null;

            if ($request->has('device_info') && $request->filled('device_info.device_id')) {
                $deviceInfo = $request->input('device_info');

                Device::create([
                    'device_id' => $deviceInfo['device_id'],
                    'brand' => $deviceInfo['brand'] ?? null,
                    'name' => $deviceInfo['name'] ?? $deviceInfo['device_id'],
                    'ipaddress' => $deviceInfo['ipaddress'] ?? $request->ip(),
                    'token' => $deviceInfo['token'] ?? null,
                    'agent_id' => $agent->id,
                    'active' => true,
                ]);
            }

            // Create auth token for API
            $token = $user->createToken('agent-auth-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Agent registered successfully',
                'data' => [
                    'user' => $user,
                    'agent' => $agent,
                    'username' => $username,
                    'token' => $token,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register agent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get billboards assigned to the authenticated agent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignedBillboards(Request $request)
    {
        $agent = $request->user()->agent;

        $billboards = $agent->billboards()
            ->with(['region', 'district', 'siteCode', 'images' => function ($query) {
                $query->latest()->take(5);
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'billboards' => $billboards
            ]
        ]);
    }

    /**
     * Upload a billboard image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadBillboardImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'billboard_id' => 'required|exists:billboards,id',
            'image' => 'required|image|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $agent = $request->user()->agent;
        $billboard = Billboard::find($request->billboard_id);

        // Check if billboard is assigned to this agent
        if ($billboard->agent_id !== $agent->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This billboard is not assigned to you'
            ], 403);
        }

        // Store the image
        $imagePath = $request->file('image')->store('billboard-images', 'public');

        // Create billboard image record
        $billboardImage = BillboardImage::create([
            'billboard_id' => $billboard->id,
            'image_path' => $imagePath,
            'uploader_id' => $agent->id,
            'uploader_type' => 'agent',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded successfully',
            'data' => [
                'image' => $billboardImage
            ]
        ]);
    }
}
