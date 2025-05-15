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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;

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
        $imageName = $this->generateImageName($billboard->name, $image->extension());
        $formattedImageName = $this->addTextToImage($image, $billboard->toArray(), $imageName);
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

    /**
     * Adds billboard metadata text to the image.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @param  array  $billboardInfo
     * @param  string  $imageName
     * @return string
     */
    protected function addTextToImage($image, array $billboardInfo, string $imageName)
    {
        $img = Image::read($image);
        $imgWidth = $img->width();
        $imgHeight = $img->height();

        $x = 0;
        $y = $imgHeight - 160;
        $currentDate = date('Y-m-d(D) H:i');

        $text = <<<EOT
Latitude: {$billboardInfo['latitude']}
Longitude: {$billboardInfo['longitude']}
{$currentDate}
EOT;

        $img->drawRectangle(
            $x,
            $y,
            fn(RectangleFactory $rectangle) =>
            $rectangle->size($imgWidth, 160)->background('#0000')
        );

        $img->text($text, $imgWidth / 2, $y + 60, function (FontFactory $font) {
            $font->filename(public_path('assets/fonts/Exo2-Bold.otf'));
            $font->size(38);
            $font->color('#FFDB58');
            $font->align('center');
            $font->valign('center');
        });

        $img->save(public_path("images/{$imageName}"));

        return $imageName;
    }

    /**
     * Generate image file name with timestamp.
     *
     * @param  string  $name
     * @param  string  $extension
     * @return string
     */
    protected function generateImageName(string $name, string $extension): string
    {
        $timestamp = now()->format('Ymd_His');
        return Str::snake($name) . "_{$timestamp}.{$extension}";
    }
}
