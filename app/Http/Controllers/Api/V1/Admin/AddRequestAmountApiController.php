<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddRequestAmountRequest;
use App\Http\Requests\UpdateAddRequestAmountRequest;
use App\Http\Resources\Admin\AddRequestAmountResource;
use App\Models\AddRequestAmount;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AddRequestAmountApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('add_request_amount_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AddRequestAmountResource(AddRequestAmount::with(['user'])->get());
    }

    public function store(StoreAddRequestAmountRequest $request)
    {
        $addRequestAmount = AddRequestAmount::create($request->all());

        return (new AddRequestAmountResource($addRequestAmount))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(AddRequestAmount $addRequestAmount)
    {
        abort_if(Gate::denies('add_request_amount_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AddRequestAmountResource($addRequestAmount->load(['user']));
    }

    public function update(UpdateAddRequestAmountRequest $request, AddRequestAmount $addRequestAmount)
    {
        $addRequestAmount->update($request->all());

        return (new AddRequestAmountResource($addRequestAmount))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(AddRequestAmount $addRequestAmount)
    {
        abort_if(Gate::denies('add_request_amount_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $addRequestAmount->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
    
    
    public function submitRequestAmount(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);
    
        $user = User::with('employee')->findOrFail($request->user_id);
    
        $addRequest = AddRequestAmount::create([
            'user_id'     => $user->id,
            'employee_id' => $user->employee ? $user->employee->id : null,
            'amount'      => $request->amount,
            'description' => $request->description,
            'status'      => 'pending',
        ]);
    
        return response()->json([
            'message' => 'Request submitted successfully',
            'data'    => $addRequest,
        ], Response::HTTP_CREATED);
    }
    
    
    public function requestHistory($userId)
    {
        $requests = AddRequestAmount::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get(['amount', 'description', 'status', 'remark', 'created_at']); // सिर्फ यही columns
    
        return response()->json([
            'data' => $requests,
        ], Response::HTTP_OK);
    }


    
    
    
    
}
