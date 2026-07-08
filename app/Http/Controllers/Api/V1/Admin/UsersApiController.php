<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;

class UsersApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new UserResource(User::with(['roles', 'companies', 'branches'])->get());
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));
        $user->companies()->sync($request->input('companies', []));
        $user->branches()->sync($request->input('branches', []));
        if ($request->input('image', false)) {
            $user->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection('image');
        }

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new UserResource($user->load(['roles', 'companies', 'branches']));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));
        $user->companies()->sync($request->input('companies', []));
        $user->branches()->sync($request->input('branches', []));
        if ($request->input('image', false)) {
            if (! $user->image || $request->input('image') !== $user->image->file_name) {
                if ($user->image) {
                    $user->image->delete();
                }
                $user->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection('image');
            }
        } elseif ($user->image) {
            $user->image->delete();
        }

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
    
    
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();

    if ($user->status == '0') {
        Auth::logout();
        return response()->json(['message' => 'Your account is inactive. Please contact admin.'], 403);
    }

    // Load relations
    $user->load(['roles', 'employee.branch', 'media']);

    // 🔹 IMAGE TRANSFORM (MAIN FIX)
    $image = null;
    $media = $user->getFirstMedia('image');
    if ($media) {
        $image = [
            'url' => $media->getFullUrl(),
        ];
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => array_merge(
            $user->toArray(),
            ['image' => $image] // 👈 overwrite image
        ),
        'roles' => $user->roles->pluck('title'),
    ]);
}


    
    
    public function getUserById($id)
{
    $user = User::with(['roles', 'employee.branch', 'media'])->find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // 🔹 IMAGE TRANSFORM
    $image = null;
    $media = $user->getFirstMedia('image');
    if ($media) {
        $image = [
            'url' => $media->getFullUrl(),
        ];
    }

    return response()->json([
        'user' => array_merge(
            $user->toArray(),
            ['image' => $image]
        ),
        'roles' => $user->roles->pluck('title'),
    ]);
}


    
    
    public function updateUserImage(Request $request, $id)
{
    Log::info('🔵 updateUserImage called', [
        'user_id' => $id,
        'has_file' => $request->hasFile('image'),
        'all_files' => $request->allFiles(),
    ]);

    try {
        $user = User::find($id);

        if (!$user) {
            Log::error('❌ User not found', ['user_id' => $id]);
            return response()->json(['message' => 'User not found'], 404);
        }

        // 🔹 Validate
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            Log::error('❌ Image validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$request->hasFile('image')) {
            Log::error('❌ image file missing in request');
            return response()->json(['message' => 'Image file not found'], 400);
        }

        // 🔹 Delete old image
        if ($user->getFirstMedia('image')) {
            Log::info('🟡 Deleting old image', [
                'media_id' => $user->getFirstMedia('image')->id
            ]);
            $user->clearMediaCollection('image');
        }

        // 🔹 Upload new image
        $media = $user
            ->addMediaFromRequest('image')
            ->toMediaCollection('image');

        Log::info('✅ Image uploaded successfully', [
            'media_id' => $media->id,
            'url' => $media->getFullUrl(),
        ]);

        return response()->json([
            'message' => 'Profile image updated successfully',
            'image' => [
                'url' => $media->getFullUrl(),
            ]
        ]);

    } catch (\Throwable $e) {
        Log::error('🔥 Image upload exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Image upload failed. Check server logs.',
        ], 500);
    }
}



}
