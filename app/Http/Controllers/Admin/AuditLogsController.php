<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('audit_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = $this->filteredQuery($request)->select(sprintf('%s.*', (new AuditLog)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'audit_log_show';
                $editGate      = 'audit_log_edit';
                $deleteGate    = 'audit_log_delete';
                $crudRoutePart = 'audit-logs';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('description', function ($row) {
                return $row->description ?: '';
            });
            $table->editColumn('action', function ($row) {
                return $row->action ? ucfirst($row->action) : '';
            });
            $table->editColumn('module', function ($row) {
                return $row->module ?: '';
            });
            $table->editColumn('subject_id', function ($row) {
                return $row->subject_id ? $row->subject_id : '';
            });
            $table->editColumn('subject_type', function ($row) {
                return $row->subject_type ? $row->subject_type : '';
            });
            $table->editColumn('user_id', function ($row) {
                return $row->actor_name ?: $row->user_id;
            });
            $table->editColumn('actor_role', function ($row) {
                return $row->actor_role ?: '';
            });
            $table->editColumn('target_user_name', function ($row) {
                return $row->target_user_name ?: '';
            });
            $table->editColumn('subject_name', function ($row) {
                return $row->subject_name ?: '';
            });
            $table->editColumn('host', function ($row) {
                return $row->host ? $row->host : '';
            });

            $table->rawColumns(['actions', 'placeholder']);

            return $table->make(true);
        }

        $roles = Role::orderBy('title')->pluck('title', 'title');
        $modules = AuditLog::moduleLabels();

        return view('admin.auditLogs.index', compact('roles', 'modules'));
    }

    public function feed(Request $request)
    {
        abort_if(Gate::denies('audit_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logs = $this->filteredQuery($request)
            ->latest()
            ->limit(200)
            ->get()
            ->map(function (AuditLog $log) {
                return [
                    'id' => $log->id,
                    'action' => ucfirst($log->action ?: str_replace('audit:', '', $log->description)),
                    'module' => $log->module ?: class_basename($log->subject_type),
                    'actor_name' => $log->actor_name ?: optional($log->user)->name ?: 'System',
                    'actor_role' => $log->actor_role ?: 'No role',
                    'target_user_name' => $log->target_user_name ?: 'N/A',
                    'subject_name' => $log->subject_name ?: 'Record #' . $log->subject_id,
                    'host' => $log->host,
                    'created_at' => optional($log->created_at)->format('d M Y, h:i A'),
                    'date' => optional($log->created_at)->format('d M Y'),
                    'time' => optional($log->created_at)->format('h:i A'),
                    'sentence' => $this->sentenceForLog($log),
                    'changes' => $log->properties,
                ];
            });

        return response()->json($logs);
    }

    private function filteredQuery(Request $request)
    {
        $from = $request->filled('from') ? $request->input('from') : now()->toDateString();
        $to = $request->filled('to') ? $request->input('to') : now()->toDateString();

        return AuditLog::query()
            ->with('user')
            ->whereNotNull('user_id')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('actor_role', 'like', '%' . $request->input('role') . '%');
            })
            ->when($request->filled('module'), function ($query) use ($request) {
                $query->where('module', $request->input('module'));
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->input('action'));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . $request->input('q') . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('actor_name', 'like', $search)
                        ->orWhere('actor_role', 'like', $search)
                        ->orWhere('target_user_name', 'like', $search)
                        ->orWhere('subject_name', 'like', $search)
                        ->orWhere('module', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            });
    }

    private function sentenceForLog(AuditLog $log): string
    {
        $actor = $log->actor_name ?: optional($log->user)->name ?: 'System';
        $role = $log->actor_role ? " ({$log->actor_role})" : '';
        $record = $log->subject_name ?: $log->target_user_name ?: 'record';
        $module = $log->module ?: class_basename($log->subject_type);
        $action = $log->action ?: str_replace('audit:', '', $log->description);

        $actionText = [
            'created' => 'create kiya',
            'updated' => 'update kiya',
            'deleted' => 'delete kiya',
        ][$action] ?? $action;

        return "{$actor}{$role} ne {$record} naam ka {$module} {$actionText}.";
    }

    public function show(AuditLog $auditLog)
    {
        abort_if(Gate::denies('audit_log_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.auditLogs.show', compact('auditLog'));
    }
}
