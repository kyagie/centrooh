<?php

namespace App\Http\Controllers;

use App\Models\{
    User,
    Agent,
    Device,
    OneTimePassword,
    Billboard,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use App\Services\BillboardImageService;

class AgentController extends Controller
{
    protected BillboardImageService $billboardImageService;

    public function __construct(BillboardImageService $billboardImageService)
    {
        $this->billboardImageService = $billboardImageService;
    }

    /**
     * Register a new agent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate request data
        $request->validate(
            [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone_number' => 'required|numeric|unique:agents,phone_number',
                'email' => 'required|email|unique:users,email',
                'device_info' => 'required|array',
                'device_info.type' => 'nullable|string',
                'device_info.brand' => 'nullable|string',
                'device_info.name' => 'nullable|string',
                'device_info.token' => 'nullable|string',
            ],
            [
                'first_name.required' => 'The first name field is required.',
                'last_name.required' => 'The last name field is required.',
                'phone_number.required' => 'The phone number field is required.',
                'phone_number.numeric' => 'The phone number must be a valid number.',
                'phone_number.unique' => 'The phone number has already been taken.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'device_info.required' => 'Device information is required.',
            ]
        );

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
                'name' => ucfirst($request->input('first_name')) . ' ' . ucfirst($request->input('last_name')),
                'email' => $request->input('email'),
                'password' => Hash::make($normalizedPhone),
            ]);

            // Assign agent role to user
            $agentRole = Role::firstOrCreate(['name' => 'agent']);
            $user->assignRole($agentRole);

            // Create agent
            $agent = Agent::create([
                'username' => $username,
                'phone_number' => $phoneNumber,
                'status' => 'inactive',
                'user_id' => $user->id,
                'created_by' => $user->id,
            ]);

            if ($request->has('device_info')) {
                $deviceInfo = $request->input('device_info');

                $device =  Device::create([
                    'device_id' => Str::uuid(),
                    'device_type' => $deviceInfo['type'] ?? null,
                    'brand' => $deviceInfo['brand'] ?? null,
                    'name' => $deviceInfo['name'] ?? null,
                    'token' => $deviceInfo['token'] ?? null,
                    'agent_id' => $agent->id,
                ]);
            }

            // Create auth token for API
            $token = $user->createToken(
                "agent-auth-token-{$device->device_id}",
                ['*'],
            )->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Agent registered successfully',
                'status' => 'registered',
                'agent' => $agent->load([
                    'user',
                ]),
                'token' => $token,
            ]);
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
            ->with(['district', 'siteCode', 'images' => function ($query) {
                $query->active();
            }])
            ->paginate(7);

        return response()->json([
            'status' => 'success',
            'billboards' => $billboards
        ]);
    }

    /**
     * Get billboard details for the authenticated agent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $billboardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBillboardDetails(Request $request, $billboardId)
    {
        $agent = $request->user()->agent;

        $billboard = $agent->billboards()
            ->with(['district', 'siteCode', 'images' => function ($query) {
                $query->active();
            }])
            ->find($billboardId);

        if (!$billboard) {
            return response()->json([
                'status' => 'error',
                'message' => 'Billboard not found or not assigned to the agent.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'billboard' => $billboard
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
        $validated = Validator::make($request->all(), [
            'billboard_id' => 'required|exists:billboards,id',
            'image' => 'required|image',
            'meta' => 'nullable|array',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validated->errors()
            ], 422);
        }

        $billboard = Billboard::find($request->billboard_id);
        $agentId = $request->user()->agent->id;

        if ($billboard->agent_id !== $agentId) {
            return response()->json([
                'status' => 'error',
                'message' => 'This billboard is not assigned to you'
            ], 403);
        }

        $image = $request->file('image');
        $imageName = $this->billboardImageService->generateImageName($billboard->name, $image->extension());
        $formattedImageName = $this->billboardImageService->addTextToImage($image, $billboard->toArray(), $imageName);
        $imagePath = public_path("images/{$formattedImageName}");

        try {
            $path = Storage::putFileAs('billboards', new File($imagePath), $imageName, 'public');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Image upload failed.',
                'error' => $e->getMessage(),
            ], 422);
        }

        if ($path) {
            $billboard->images()->create([
                'image_path' => $path,
                'image_type' => 'billboard',
                'status' => 'pending',
                'uploader_type' => 'agent',
                'is_primary' => false,
                'meta' => $request->input('meta'),
                'agent_id' => $agentId,
            ]);

            $billboard->update(['status' => 'in_review']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded successfully',
        ]);
    }

    //All agent notifications
    public function getNotifications(Request $request)
    {
        $agent = $request->user()->agent;

        $notifications = $agent->notifications()
            ->with(['agentNotificationType'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'notifications' => $notifications
        ]);
    }
    
    //Unread notifications count
    public function unreadNotificationsCount(Request $request)
    {
        $agent = $request->user()->agent;

        $unreadCount = $agent->notifications()
            ->unread()
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount
        ]);
    }

    //notification details
    public function notificationDetails(Request $request, $notificationId)
    {
        $agent = $request->user()->agent;

        $notification = $agent->notifications()
            ->where('id', $notificationId)
            ->with(['type'])
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'notification' => $notification
        ]);
    }
}
