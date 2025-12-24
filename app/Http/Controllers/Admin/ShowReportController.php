<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyShowReportRequest;
use App\Http\Requests\StoreShowReportRequest;
use App\Http\Requests\UpdateShowReportRequest;
use App\Models\ShowReport;
use App\Models\User;
use App\Models\Employee;
use App\Models\PerformanceReport;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class ShowReportController extends Controller
{

  public function index(Request $request)
{
    abort_if(Gate::denies('show_report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $employees = \App\Models\Employee::all();
    $showReports = \App\Models\ShowReport::with(['select_employess']);

    $selectedEmployee = $request->employee_id;
    $selectedMonths   = $request->months ?? [];
    $performanceReports = collect();
    $finalPoints   = 0;
    $profitPoints  = 0;
    $monthlyData   = collect();

    if ($selectedEmployee && !empty($selectedMonths)) {
        $performanceReports = \App\Models\PerformanceReport::where('employee_id', $selectedEmployee)
            ->where(function ($query) use ($selectedMonths) {
                foreach ($selectedMonths as $m) {
                    $query->orWhereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$m]);
                }
            })
            ->get();

        // Group by Month
        $monthlyData = $performanceReports->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->date)->format('Y-m');
        })->map(function ($monthReports) {
            $totalFinalPoints = $monthReports->sum('final_points'); // use final_points
            $totalCredit      = $monthReports->sum('half_unpaid_points'); // credit/half unpaid
            $final            = $totalFinalPoints + $totalCredit;

            return [
                'final_points' => $totalFinalPoints, // profit points replaced by final_points
                'total_credit' => $totalCredit,
                'final'        => $final,
            ];
        });

        // Averages for gauge
        $profitPoints = round($monthlyData->avg('final_points'));
        $finalPoints  = round($monthlyData->avg('final'));
    }

    $showReports = $showReports->get();

    return view('admin.showReports.index', compact(
        'showReports',
        'employees',
        'performanceReports',
        'selectedEmployee',
        'selectedMonths',
        'finalPoints',
        'profitPoints',
        'monthlyData'
    ));
}



    public function create()
    {
        abort_if(Gate::denies('show_report_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $select_employesses = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.showReports.create', compact('select_employesses'));
    }

    public function store(StoreShowReportRequest $request)
    {
        $showReport = ShowReport::create($request->all());

        return redirect()->route('admin.show-reports.index');
    }

    public function edit(ShowReport $showReport)
    {
        abort_if(Gate::denies('show_report_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $select_employesses = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $showReport->load('select_employess', 'created_by');

        return view('admin.showReports.edit', compact('select_employesses', 'showReport'));
    }

    public function update(UpdateShowReportRequest $request, ShowReport $showReport)
    {
        $showReport->update($request->all());

        return redirect()->route('admin.show-reports.index');
    }

    public function show(ShowReport $showReport)
    {
        abort_if(Gate::denies('show_report_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $showReport->load('select_employess', 'created_by');

        return view('admin.showReports.show', compact('showReport'));
    }

    public function destroy(ShowReport $showReport)
    {
        abort_if(Gate::denies('show_report_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $showReport->delete();

        return back();
    }

    public function massDestroy(MassDestroyShowReportRequest $request)
    {
        $showReports = ShowReport::find(request('ids'));

        foreach ($showReports as $showReport) {
            $showReport->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
