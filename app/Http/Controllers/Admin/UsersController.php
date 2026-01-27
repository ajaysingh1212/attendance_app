<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

public function index()
{
    abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $users = User::with(['roles', 'companies', 'branches', 'media'])->get();
    

    return view('admin.users.index', compact('users'));
}




    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::pluck('title', 'id');

        $companies = Company::pluck('title', 'id');

        $branches = Branch::pluck('title', 'id');

        return view('admin.users.create', compact('branches', 'companies', 'roles'));
    }

public function store(StoreUserRequest $request)
{
    $data = $request->all();

    // ✅ Master password hash (only if present)
    if ($request->filled('master_password')) {
        $data['master_password'] = Hash::make($request->master_password);
    } else {
        unset($data['master_password']);
    }

    $user = User::create($data);

    $user->roles()->sync($request->input('roles', []));
    $user->companies()->sync($request->input('companies', []));
    $user->branches()->sync($request->input('branches', []));

    if ($request->input('image', false)) {
        $user->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))
             ->toMediaCollection('image');
    }

    if ($media = $request->input('ck-media', false)) {
        Media::whereIn('id', $media)->update(['model_id' => $user->id]);
    }

    return redirect()->route('admin.users.index');
}


    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::pluck('title', 'id');

        $companies = Company::pluck('title', 'id');

        $branches = Branch::pluck('title', 'id');

        $user->load('roles', 'companies', 'branches');

        return view('admin.users.edit', compact('branches', 'companies', 'roles', 'user'));
    }

public function update(UpdateUserRequest $request, User $user)
{
    $data = $request->all();

    // 🔐 Handle master_password securely
    if ($request->filled('master_password')) {
        $data['master_password'] = Hash::make($request->master_password);
    } else {
        unset($data['master_password']); // do not overwrite existing hash
    }


    // 🔒 Allow master_password update ONLY if Admin role is selected
    $adminRoleId = Role::where('title', 'Admin')->value('id');

    if (!in_array($adminRoleId, $request->input('roles', []))) {
        unset($data['master_password']);
    }


    $user->update($data);

    $user->roles()->sync($request->input('roles', []));
    $user->companies()->sync($request->input('companies', []));
    $user->branches()->sync($request->input('branches', []));

    // 🖼 Image handling (unchanged)
    if ($request->input('image', false)) {
        if (! $user->image || $request->input('image') !== $user->image->file_name) {
            if ($user->image) {
                $user->image->delete();
            }
            $user->addMedia(
                storage_path('tmp/uploads/' . basename($request->input('image')))
            )->toMediaCollection('image');
        }
    } elseif ($user->image) {
        $user->image->delete();
    }

    return redirect()->route('admin.users.index');
}


    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load('roles', 'companies', 'branches');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $users = User::find(request('ids'));

        foreach ($users as $user) {
            $user->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('user_create') && Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new User();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
public function termsStatus($employee_id)
{
    $employee = Employee::findOrFail($employee_id);
    $user = User::findOrFail($employee->user_id);

    return response()->json([
        'accepted' => (bool) $user->terms_accepted
    ]);
}


public function savePhoto(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'photo'       => 'required|image',
        'lat'         => 'nullable',
        'lng'         => 'nullable',
        'address'     => 'nullable'
    ]);

    $employee = Employee::findOrFail($request->employee_id);
    $user = User::findOrFail($employee->user_id);

    // OLD PHOTO REMOVE
    $user->clearMediaCollection('accept_image');

    // SAVE PHOTO
    $user->addMediaFromRequest('photo')
         ->usingFileName('accept_image.jpg')
         ->toMediaCollection('accept_image');

    // ✅ SAVE LOCATION IN USER TABLE
    $user->update([
        'latitude'            => $request->lat,
        'longitude'           => $request->lng,
        'current_address'     => $request->address,
        'location_verified_at'=> now(),
    ]);

    return response()->json(['status' => true]);
}




public function saveSignature(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'signature'   => 'required',
        'lat'         => 'nullable',
        'lng'         => 'nullable',
        'address'     => 'nullable'
    ]);

    $employee = Employee::findOrFail($request->employee_id);
    $user = User::findOrFail($employee->user_id);

    // SAVE SIGNATURE
    $image = str_replace('data:image/png;base64,', '', $request->signature);
    $image = base64_decode($image);

    $user->clearMediaCollection('sign_image');
    $user->addMediaFromString($image)
         ->usingFileName('signature.png')
         ->toMediaCollection('sign_image');

    // UPDATE TERMS + LOCATION
    $user->update([
        'terms_accepted'       => 1,
        'latitude'             => $request->lat ?? $user->latitude,
        'longitude'            => $request->lng ?? $user->longitude,
        'current_address'      => $request->address ?? $user->current_address,
        'location_verified_at' => now(),
    ]);

    return response()->json(['status' => true]);
}



}
