<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyAddRequestAmountRequest;
use App\Http\Requests\StoreAddRequestAmountRequest;
use App\Http\Requests\UpdateAddRequestAmountRequest;
use App\Models\AddRequestAmount;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Employee;

class AddRequestAmountController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
{
    abort_if(Gate::denies('add_request_amount_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    if ($request->ajax()) {
        $query = AddRequestAmount::with(['employee'])->select(sprintf('%s.*', (new AddRequestAmount)->table));
        $table = Datatables::of($query);

        $table->addColumn('placeholder', '&nbsp;');
        $table->addColumn('actions', '&nbsp;');

        $table->editColumn('actions', function ($row) {
            $viewGate      = 'add_request_amount_show';
            $editGate      = 'add_request_amount_edit';
            $deleteGate    = 'add_request_amount_delete';
            $crudRoutePart = 'add-request-amounts';

            return view('partials.datatablesActions', compact(
                'viewGate',
                'editGate',
                'deleteGate',
                'crudRoutePart',
                'row'
            ));
        });

        $table->editColumn('id', function ($row) {
            return $row->id ?? '';
        });

        $table->addColumn('employee_name', function ($row) {
            return $row->employee ? $row->employee->full_name : '';
        });

        $table->editColumn('amount', function ($row) {
            return $row->amount ?? '';
        });

        $table->editColumn('description', function ($row) {
            return $row->description ?? '';
        });

        $table->editColumn('status', function ($row) {
            return $row->status ? AddRequestAmount::STATUS_SELECT[$row->status] : '';
        });

        $table->editColumn('remark', function ($row) {
            return $row->remark ?? '';
        });

        $table->rawColumns(['actions', 'placeholder', 'employee_name']);

        return $table->make(true);
    }

    return view('admin.addRequestAmounts.index');
}

  public function create()
{
    abort_if(Gate::denies('add_request_amount_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // Employee list with full_name
    $employees = Employee::pluck('full_name', 'id')->prepend(trans('global.pleaseSelect'), '');

    return view('admin.addRequestAmounts.create', compact('employees'));
}

public function store(StoreAddRequestAmountRequest $request)
{
    $data = $request->all();

    // ensure only employee_id is saved instead of user_id
    $addRequestAmount = AddRequestAmount::create($data);

    return redirect()->route('admin.add-request-amounts.index');
}


   public function edit(AddRequestAmount $addRequestAmount)
{
    abort_if(Gate::denies('add_request_amount_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // Employees list
    $employees = Employee::pluck('full_name', 'id')->prepend(trans('global.pleaseSelect'), '');

    // Load employee relation
    $addRequestAmount->load('employee');

    return view('admin.addRequestAmounts.edit', compact('addRequestAmount', 'employees'));
}

public function update(UpdateAddRequestAmountRequest $request, AddRequestAmount $addRequestAmount)
{
    $addRequestAmount->update($request->all());

    return redirect()->route('admin.add-request-amounts.index');
}


  public function show(AddRequestAmount $addRequestAmount)
{
    abort_if(Gate::denies('add_request_amount_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $addRequestAmount->load('employee');

    return view('admin.addRequestAmounts.show', compact('addRequestAmount'));
}


    public function destroy(AddRequestAmount $addRequestAmount)
    {
        abort_if(Gate::denies('add_request_amount_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $addRequestAmount->delete();

        return back();
    }

    public function massDestroy(MassDestroyAddRequestAmountRequest $request)
    {
        $addRequestAmounts = AddRequestAmount::find(request('ids'));

        foreach ($addRequestAmounts as $addRequestAmount) {
            $addRequestAmount->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
