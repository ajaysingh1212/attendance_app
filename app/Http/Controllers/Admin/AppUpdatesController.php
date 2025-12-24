<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyAppUpdateRequest;
use App\Http\Requests\StoreAppUpdateRequest;
use App\Http\Requests\UpdateAppUpdateRequest;
use App\Models\AppUpdate;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AppUpdatesController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('app_update_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = AppUpdate::query()->select(sprintf('%s.*', (new AppUpdate)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'app_update_show';
                $editGate      = 'app_update_edit';
                $deleteGate    = 'app_update_delete';
                $crudRoutePart = 'app-updates';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('version', function ($row) {
                return $row->version ? $row->version : '';
            });
            $table->editColumn('heading', function ($row) {
                return $row->heading ? $row->heading : '';
            });
            $table->editColumn('app', function ($row) {
                return $row->app ? '<a href="' . $row->app->getUrl() . '" target="_blank">' . trans('global.downloadFile') . '</a>' : '';
            });

            $table->rawColumns(['actions', 'placeholder', 'app']);

            return $table->make(true);
        }

        return view('admin.appUpdates.index');
    }

    public function create()
    {
        abort_if(Gate::denies('app_update_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.appUpdates.create');
    }

    public function store(StoreAppUpdateRequest $request)
    {
        $appUpdate = AppUpdate::create($request->all());

        if ($request->input('app', false)) {
            $appUpdate->addMedia(storage_path('tmp/uploads/' . basename($request->input('app'))))->toMediaCollection('app');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $appUpdate->id]);
        }

        return redirect()->route('admin.app-updates.index');
    }

    public function edit(AppUpdate $appUpdate)
    {
        abort_if(Gate::denies('app_update_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.appUpdates.edit', compact('appUpdate'));
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

        return redirect()->route('admin.app-updates.index');
    }

    public function show(AppUpdate $appUpdate)
    {
        abort_if(Gate::denies('app_update_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.appUpdates.show', compact('appUpdate'));
    }

    public function destroy(AppUpdate $appUpdate)
    {
        abort_if(Gate::denies('app_update_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $appUpdate->delete();

        return back();
    }

    public function massDestroy(MassDestroyAppUpdateRequest $request)
    {
        $appUpdates = AppUpdate::find(request('ids'));

        foreach ($appUpdates as $appUpdate) {
            $appUpdate->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('app_update_create') && Gate::denies('app_update_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new AppUpdate();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
public function storeMedia(Request $request)
{
    abort_if(Gate::denies('app_update_create') && Gate::denies('app_update_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // Max size validation
    if ($request->has('size')) {
        $request->validate([
            'file' => 'max:' . ($request->input('size') * 1024), // size in KB
        ]);
    }

    // APK file validation - use mimetypes instead of mimes
    $request->validate([
        'file' => 'required|file|mimetypes:application/vnd.android.package-archive|max:102400', // 100 MB in KB
    ]);


    // Save file temporarily
    $path = storage_path('tmp/uploads');
    if (!file_exists($path)) mkdir($path, 0755, true);

    $file = $request->file('file');
    $filename = uniqid() . '_' . trim($file->getClientOriginalName());

    $file->move($path, $filename);

    // Add to MediaLibrary
    $model = new AppUpdate();
    $media = $model->addMedia($path . '/' . $filename)->toMediaCollection('app');

    // Return JSON for JS
    return response()->json([
        'id' => $media->id,
        'name' => $filename,
        'original_name' => $file->getClientOriginalName(),
        'url' => $media->getUrl(),
    ], Response::HTTP_CREATED);
}


}
