<?php



namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::all();
        return view('admin.leave_type.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('admin.leave_type.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:leave_types,name',
        ]);

        LeaveType::create($request->all());
        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave_type.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name' => 'required|unique:leave_types,name,' . $leaveType->id,
        ]);

        $leaveType->update($request->all());
        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type updated successfully.');
    }
    public function show(LeaveType $leaveType)
{
    return view('admin.leave_type.show', compact('leaveType'));
}


    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->back()->with('success', 'Leave type deleted successfully.');
    }
}
