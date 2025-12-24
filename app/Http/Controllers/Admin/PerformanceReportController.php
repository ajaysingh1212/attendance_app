<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Employee;
use App\Models\PerformanceReport;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PerformanceReportController extends Controller
{

    public function index()
    {
        abort_if(Gate::denies('performence_report_index'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $reports = PerformanceReport::with(['created_by'])->get();
       

        return view('admin.reports.index', compact('reports'));
    }

    public function create()
    {
        abort_if(Gate::denies('performence_report_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        

        // Fetch all employees for dropdown
        $employees = Employee::select('id', 'full_name')->orderBy('full_name', 'asc')->get();
       
        return view('admin.reports.create', compact('employees'));
    }

public function store(StoreReportRequest $request)
{
    $data = $request->all();
    $data['created_by_id'] = auth()->id();

    // 🔹 Report date से month निकालो
    $month = \Carbon\Carbon::parse($request->date)->format('Y-m');

    // 🔹 Check करो कि उसी employee का उसी month में report already है या नहीं
    $exists = PerformanceReport::where('employee_id', $request->employee_id)
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month])
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'employee_id' => 'This employee already has a performance report for this month.'
        ])->withInput();
    }

    // File upload
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('uploads/performance_reports'), $filename);
        $data['attachment'] = 'uploads/performance_reports/'.$filename;
    }

    PerformanceReport::create($data);

    return redirect()->route('admin.performance-reports.index')->with('success', 'Report created successfully.');
}


    public function edit(PerformanceReport $performanceReport)
    {
        abort_if(Gate::denies('performence_report_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Load relations if needed
        $performanceReport->load('created_by', 'employee');

        // Fetch all employees for dropdown
        $employees = Employee::select('id', 'full_name')->orderBy('full_name', 'asc')->get();

        return view('admin.reports.edit', compact('performanceReport', 'employees'));
    }

public function update(UpdateReportRequest $request, PerformanceReport $performanceReport)
{
    $data = $request->all();

    // File upload
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('uploads/performance_reports'), $filename);
        $data['attachment'] = 'uploads/performance_reports/'.$filename;
    }

    $performanceReport->update($data);

    return redirect()->route('admin.performance-reports.index');
}


    public function show(PerformanceReport  $performanceReport)
    {
        abort_if(Gate::denies('performence_report_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $performanceReport->load('created_by');
         $employees = Employee::select('id', 'full_name')->orderBy('full_name', 'asc')->get();
        return view('admin.reports.show', compact('performanceReport', 'employees'));
    }

    public function destroy(PerformanceReport $performanceReport)
    {
        abort_if(Gate::denies('performence_report_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

       $performanceReport->delete();

        return back();
    }

    public function massDestroy(MassDestroyReportRequest $request)
    {
        $reports = PerformanceReport::find(request('ids'));

        foreach ($reports as $report) {
            $report->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
