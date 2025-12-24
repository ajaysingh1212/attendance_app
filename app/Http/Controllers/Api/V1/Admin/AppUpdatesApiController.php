<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreAppUpdateRequest;
use App\Http\Requests\UpdateAppUpdateRequest;
use App\Http\Resources\Admin\AppUpdateResource;
use App\Models\AppUpdate;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppUpdatesApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('app_update_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AppUpdateResource(AppUpdate::all());
    }

    public function store(StoreAppUpdateRequest $request)
    {
        $appUpdate = AppUpdate::create($request->all());

        if ($request->input('app', false)) {
            $appUpdate->addMedia(storage_path('tmp/uploads/' . basename($request->input('app'))))->toMediaCollection('app');
        }

        return (new AppUpdateResource($appUpdate))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(AppUpdate $appUpdate)
    {
        abort_if(Gate::denies('app_update_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AppUpdateResource($appUpdate);
    }

    public function update(UpdateAppUpdateRequest $request, AppUpdate $appUpdate)
    {
        $appUpdate->update($request->all());

        if ($request->input('app', false)) {
            if (! $appUpdate->app || $request->input('app') !== $appUpdate->app->file_name) {
                if ($appUpdate->app) {
                    $appUpdate->app->delete();
                }
                $appUpdate->addMedia(storage_path('tmp/uploads/' . basename($request->input('app'))))->toMediaCollection('app');
            }
        } elseif ($appUpdate->app) {
            $appUpdate->app->delete();
        }

        return (new AppUpdateResource($appUpdate))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(AppUpdate $appUpdate)
    {
        abort_if(Gate::denies('app_update_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $appUpdate->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
