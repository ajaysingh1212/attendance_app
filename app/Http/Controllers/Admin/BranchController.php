<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyBranchRequest;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
{
    abort_if(Gate::denies('branch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    if ($request->ajax()) {
        $query = Branch::query()->select(sprintf('%s.*', (new Branch)->table));
        $table = Datatables::of($query);

        $table->addColumn('placeholder', '&nbsp;');
        $table->addColumn('actions', '&nbsp;');

        /* ================= ACTIONS ================= */
        $table->editColumn('actions', function ($row) {
            $viewGate      = 'branch_show';
            $editGate      = 'branch_edit';
            $deleteGate    = 'branch_delete';
            $crudRoutePart = 'branches';

            return view('partials.datatablesActions', compact(
                'viewGate',
                'editGate',
                'deleteGate',
                'crudRoutePart',
                'row'
            ));
        });

        /* ================= BASIC FIELDS ================= */
        $table->editColumn('id', fn ($row) => $row->id ?? '');
        $table->editColumn('title', fn ($row) => $row->title ?? '');
        $table->editColumn('address', fn ($row) => $row->address ?? '');
        $table->editColumn('legal_name', fn ($row) => $row->legal_name ?? '');
        $table->editColumn('incharge_name', fn ($row) => $row->incharge_name ?? '');
        $table->editColumn('email', fn ($row) => $row->email ?? '');
        $table->editColumn('phone', fn ($row) => $row->phone ?? '');
        $table->editColumn('gst', fn ($row) => $row->gst ?? '');
        $table->editColumn('pan', fn ($row) => $row->pan ?? '');
        $table->editColumn('registration_number', fn ($row) => $row->registration_number ?? '');

        /* ================= IMAGES (MEDIA) ================= */

        // Branch Logo
        $table->addColumn('branch_image', function ($row) {
            if ($row->branch_image) {
                return '<a href="'.$row->branch_image->url.'" target="_blank">
                            <img src="'.$row->branch_image->thumbnail.'" width="50" height="50">
                        </a>';
            }
            return '';
        });

        // Signature Image
        $table->addColumn('signature_image', function ($row) {
            if ($row->signature_image) {
                return '<a href="'.$row->signature_image->url.'" target="_blank">
                            <img src="'.$row->signature_image->url.'" width="50" height="50">
                        </a>';
            }
            return '';
        });

        // Stamp Image
        $table->addColumn('stamp_image', function ($row) {
            if ($row->stamp_image) {
                return '<a href="'.$row->stamp_image->url.'" target="_blank">
                            <img src="'.$row->stamp_image->url.'" width="50" height="50">
                        </a>';
            }
            return '';
        });

        /* ================= RAW COLUMNS ================= */
        $table->rawColumns([
            'actions',
            'placeholder',
            'branch_image',
            'signature_image',
            'stamp_image'
        ]);

        return $table->make(true);
    }

    return view('admin.branches.index');
}

    public function create()
    {
        abort_if(Gate::denies('branch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.branches.create');
    }

public function store(StoreBranchRequest $request)
{
    $branch = Branch::create($request->all());

    if ($request->hasFile('branch_image')) {
        $branch->addMediaFromRequest('branch_image')->toMediaCollection('branch_image');
    }

    if ($request->hasFile('signature')) {
        $branch->addMediaFromRequest('signature')->toMediaCollection('signature');
    }

    if ($request->hasFile('stamp')) {
        $branch->addMediaFromRequest('stamp')->toMediaCollection('stamp');
    }

    return redirect()->route('admin.branches.index')->with('success','Branch created');
}

    public function edit(Branch $branch)
    {
        abort_if(Gate::denies('branch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.branches.edit', compact('branch'));
    }

public function update(UpdateBranchRequest $request, Branch $branch)
{
    $branch->update($request->all());

    if ($request->hasFile('branch_image')) {
        $branch->clearMediaCollection('branch_image');
        $branch->addMediaFromRequest('branch_image')->toMediaCollection('branch_image');
    }

    if ($request->hasFile('signature')) {
        $branch->clearMediaCollection('signature');
        $branch->addMediaFromRequest('signature')->toMediaCollection('signature');
    }

    if ($request->hasFile('stamp')) {
        $branch->clearMediaCollection('stamp');
        $branch->addMediaFromRequest('stamp')->toMediaCollection('stamp');
    }

    return redirect()->route('admin.branches.index')->with('success','Branch updated');
}

    public function show(Branch $branch)
    {
        abort_if(Gate::denies('branch_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.branches.show', compact('branch'));
    }

    public function destroy(Branch $branch)
    {
        abort_if(Gate::denies('branch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branch->delete();

        return back();
    }

    public function massDestroy(MassDestroyBranchRequest $request)
    {
        $branches = Branch::find(request('ids'));

        foreach ($branches as $branch) {
            $branch->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('branch_create') && Gate::denies('branch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Branch();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
