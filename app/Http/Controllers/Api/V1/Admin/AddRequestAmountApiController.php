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
}
