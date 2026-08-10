<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeBranch;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfficeBranchController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('branch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $offices = OfficeBranch::latest()->paginate(25);

        return view('admin.officeBranches.index', compact('offices'));
    }

    public function create()
    {
        abort_if(Gate::denies('branch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.officeBranches.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('branch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        OfficeBranch::create($this->validatedData($request));

        return redirect()->route('admin.office-branches.index')->with('success', 'Office saved successfully.');
    }

    public function show(OfficeBranch $officeBranch)
    {
        abort_if(Gate::denies('branch_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.officeBranches.show', compact('officeBranch'));
    }

    public function edit(OfficeBranch $officeBranch)
    {
        abort_if(Gate::denies('branch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.officeBranches.edit', compact('officeBranch'));
    }

    public function update(Request $request, OfficeBranch $officeBranch)
    {
        abort_if(Gate::denies('branch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $officeBranch->update($this->validatedData($request));

        return redirect()->route('admin.office-branches.index')->with('success', 'Office updated successfully.');
    }

    public function destroy(OfficeBranch $officeBranch)
    {
        abort_if(Gate::denies('branch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($officeBranch->employees()->exists() || $officeBranch->payrolls()->exists()) {
            return back()->with('error', 'This office is assigned to employees/payrolls and cannot be deleted.');
        }

        $officeBranch->delete();

        return back()->with('success', 'Office deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'branch_name' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'address_line' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'registration_detail' => 'nullable|string|max:2000',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'legal_entity_name' => 'nullable|string|max:255',
            'incharge_name' => 'required|string|max:255',
            'incharge_phone' => 'required|string|max:30',
            'incharge_email' => 'nullable|email|max:255',
        ]);
    }
}
