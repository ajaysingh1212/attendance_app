<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class LeaveRequestController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
{
    abort_if(Gate::denies('leave_request_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    if ($request->ajax()) {
        $query = LeaveRequest::with(['user', 'leaveType'])->select(sprintf('%s.*', (new LeaveRequest)->table));
        $table = Datatables::of($query);

        $table->addColumn('placeholder', '&nbsp;');
        $table->addColumn('actions', '&nbsp;');

        $table->editColumn('actions', function ($row) {
            $viewGate      = 'leave_request_show';
            $editGate      = 'leave_request_edit';
            $deleteGate    = 'leave_request_delete';
            $crudRoutePart = 'leave-requests';

            return view('partials.datatablesActions', compact(
                'viewGate',
                'editGate',
                'deleteGate',
                'crudRoutePart',
                'row'
            ));
        });

        $table->editColumn('id', fn($row) => $row->id ?: '');

        $table->addColumn('user_name', fn($row) => $row->user?->name ?? '');

        $table->addColumn('leave_type_name', fn($row) => $row->leaveType?->name ?? '');

        $table->editColumn('title', fn($row) => $row->title ?? '');
        $table->editColumn('date_from', fn($row) => $row->date_from ?? '');
        $table->editColumn('date_to', fn($row) => $row->date_to ?? '');

        $table->editColumn('attachment', function ($row) {
            if ($photo = $row->attachment) {
                return sprintf(
                    '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                    $photo->url,
                    $photo->thumbnail
                );
            }

            return '';
        });

        $table->editColumn('status', fn($row) => $row->status ? LeaveRequest::STATUS_SELECT[$row->status] : '');

        $table->rawColumns(['actions', 'placeholder', 'user', 'leave_type_name', 'attachment']);

        return $table->make(true);
    }

    return view('admin.leaveRequests.index');
}


        public function create()
        {
            abort_if(Gate::denies('leave_request_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

            $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
            $leaveTypes = \App\Models\LeaveType::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

            return view('admin.leaveRequests.create', compact('users', 'leaveTypes'));
        }


public function store(StoreLeaveRequestRequest $request)
{
    $data = $request->all();
    $data['status'] = 'pending'; // Set default status

    $leaveRequest = LeaveRequest::create($data);

    if ($request->input('attachment', false)) {
        $leaveRequest->addMedia(storage_path('tmp/uploads/' . basename($request->input('attachment'))))
            ->toMediaCollection('attachment');
    }

    if ($media = $request->input('ck-media', false)) {
        Media::whereIn('id', $media)->update(['model_id' => $leaveRequest->id]);
    }

    return redirect()->route('admin.leave-requests.index')->with('success', 'Leave Request submitted successfully!');
}


    public function edit(LeaveRequest $leaveRequest)
    {
        abort_if(Gate::denies('leave_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $leaveTypes = LeaveType::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.leaveRequests.edit', compact('leaveRequest', 'users', 'leaveTypes'));
    }

public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest)
{
    $leaveRequest->update($request->all());

    if ($request->input('attachment', false)) {
        if (!$leaveRequest->attachment || $request->input('attachment') !== $leaveRequest->attachment->file_name) {
            $leaveRequest->addMedia(storage_path('tmp/uploads/' . basename($request->input('attachment'))))
                ->toMediaCollection('attachment');
        }
    } elseif ($leaveRequest->attachment) {
        $leaveRequest->attachment->delete();
    }

    return redirect()->route('admin.leave-requests.index')->with('success', 'Leave Request updated successfully!');
}

    public function show(LeaveRequest $leaveRequest)
    {
        abort_if(Gate::denies('leave_request_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $leaveRequest->load('user');

        return view('admin.leaveRequests.show', compact('leaveRequest'));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        abort_if(Gate::denies('leave_request_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $leaveRequest->delete();

        return back();
    }

    public function massDestroy(MassDestroyLeaveRequestRequest $request)
    {
        $leaveRequests = LeaveRequest::find(request('ids'));

        foreach ($leaveRequests as $leaveRequest) {
            $leaveRequest->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('leave_request_create') && Gate::denies('leave_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new LeaveRequest();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
