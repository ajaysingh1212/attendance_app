<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use App\Models\TrackMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    use CsvImportTrait;

public function index()
{
    abort_if(\Gate::denies('report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // ✅ reports लाओ
    $reports = Report::latest()->paginate(20);

    // ✅ users लाओ (dropdown के लिए)
    $users = \App\Models\User::all();

    return view('admin.reports.index', compact('reports', 'users'));
}


public function fetchTrackHistory(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $trackData = TrackMember::where('user_id', $request->user_id)
        ->whereBetween('created_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59',
        ])
        ->orderBy('created_at')
        ->get(['latitude', 'longitude', 'location', 'created_at']);

    return response()->json($trackData);
}


    public function massDestroy(MassDestroyReportRequest $request)
    {
        $reports = Report::find(request('ids'));

        foreach ($reports as $report) {
            $report->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
