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
    
        $user = Auth::user()->load(['roles', 'employee.branch']); // roles + employee + branch
        $token = $user->createToken('api-token')->plainTextToken;
    
        return response()->json([
        'token'  => $token,
        'user'   => $user,
        'roles'  => $user->roles->pluck('title'),
        // 'branch' => $user->employee ? $user->employee->branch : null, // ye line hata do
        ]);
    }

    
    
    public function getUserById($id)
    {
        // Load roles, employee and branch (same as login)
        $user = User::with(['roles', 'employee.branch', 'media'])->find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Return in same structure as login
        return response()->json([
            'user'  => $user,
            'roles' => $user->roles->pluck('title'),
        ]);
    }
    
    
    public function updateUserImage(Request $request, $id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Validate that an image file is sent
        $request->validate([
            'image' => 'required|image|max:2048', // max 2MB
        ]);
    
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                $user->image->delete();
            }
    
            // Add new image to media collection
            $user->addMediaFromRequest('image')->toMediaCollection('image');
        }
    
        return response()->json([
            'message' => 'Profile image updated successfully',
            'image_url' => $user->getFirstMediaUrl('image'),
        ]);
    }


}
