<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Http\Resources\Admin\LeaveRequestResource;
use App\Models\LeaveRequest;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LeaveRequestApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('leave_request_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new LeaveRequestResource(LeaveRequest::with(['user'])->get());
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $leaveRequest = LeaveRequest::create($request->all());

        if ($request->input('attachment', false)) {
            $leaveRequest->addMedia(storage_path('tmp/uploads/' . basename($request->input('attachment'))))->toMediaCollection('attachment');
        }

        return (new LeaveRequestResource($leaveRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        abort_if(Gate::denies('leave_request_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new LeaveRequestResource($leaveRequest->load(['user']));
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update($request->all());

        if ($request->input('attachment', false)) {
            if (! $leaveRequest->attachment || $request->input('attachment') !== $leaveRequest->attachment->file_name) {
                if ($leaveRequest->attachment) {
                    $leaveRequest->attachment->delete();
                }
                $leaveRequest->addMedia(storage_path('tmp/uploads/' . basename($request->input('attachment'))))->toMediaCollection('attachment');
            }
        } elseif ($leaveRequest->attachment) {
            $leaveRequest->attachment->delete();
        }

        return (new LeaveRequestResource($leaveRequest))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        abort_if(Gate::denies('leave_request_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $leaveRequest->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
    
    
    public function submitLeaveRequest(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'status'      => 'nullable|in:pending,approved,reject',
            'remark'      => 'nullable|string',
            'attachment'  => 'nullable|string', // file path from tmp upload
        ]);
    
        $leaveRequest = LeaveRequest::create($request->all());
    
        if ($request->input('attachment')) {
            $leaveRequest
                ->addMedia(storage_path('tmp/uploads/' . basename($request->input('attachment'))))
                ->toMediaCollection('attachment');
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data'    => new \App\Http\Resources\Admin\LeaveRequestResource($leaveRequest),
        ], 201);
    }
    
    public function getLeaveRequestsByUser($userId)
    {
        // Check if user exists
        if (!\App\Models\User::find($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        $leaveRequests = LeaveRequest::where('user_id', $userId)->latest()->get();
    
        // Status-wise counts
        $counts = [
            'pending'  => LeaveRequest::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('user_id', $userId)->where('status', 'approved')->count(),
            'reject'   => LeaveRequest::where('user_id', $userId)->where('status', 'reject')->count(),
        ];
    
        return response()->json([
            'success' => true,
            'message' => 'Leave requests fetched successfully',
            'counts'  => $counts,
            'data'    => LeaveRequestResource::collection($leaveRequests),
        ]);
    }



}
