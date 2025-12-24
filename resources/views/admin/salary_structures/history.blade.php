@extends('layouts.admin')

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .card-header {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: #fff;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
        }
        #salaryHistoryTable th {
            background: #343a40 !important;
            color: #fff;
            text-align: center;
        }
        #salaryHistoryTable td {
            vertical-align: middle;
        }
        .see-more-btn {
            font-size: 0.85rem;
            color: #0d6efd;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
        }
        .see-more-btn:hover {
            text-decoration: underline;
        }
        .modal-header {
            background: #007bff;
            color: #fff;
        }
        .snapshot-table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            text-align: left;
        }
        .snapshot-table td {
            vertical-align: middle;
            font-size: 14px;
        }
        .modal-body .card {
            border-radius: 10px;
        }
        .modal-body .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Salary Structure History</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="salaryHistoryTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Employee</th>
                            <th style="width: 20%;">Created At</th>
                            <th>Snapshot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($histories as $index => $history)
                            @php
                                $snapshot = is_string($history->structure_snapshot) 
                                    ? json_decode($history->structure_snapshot, true) 
                                    : $history->structure_snapshot;

                                $visibleKeys = [
                                    'employee_code',
                                    'employee_name',
                                    'basic',
                                    'hra',
                                    'other_deductions',
                                    'net_salary',
                                    'type',
                                    'adjustment_amount'
                                ];

                                $hiddenRows = array_diff_key($snapshot ?? [], array_flip($visibleKeys));
                            @endphp

                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td>{{ $history->employee->full_name ?? $history->employee->name ?? 'N/A' }}</td>
                                <td>{{ $history->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <table class="table table-sm mb-0">
                                        @foreach ($snapshot ?? [] as $key => $value)
                                            @if(in_array($key, $visibleKeys))
                                                <tr>
                                                    <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                    <td>{{ $value }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </table>

                                    @if(count($hiddenRows))
                                        <!-- See More Button -->
                                        <button class="see-more-btn mt-1" data-bs-toggle="modal" data-bs-target="#moreModal-{{ $history->id }}">
                                            See More
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="moreModal-{{ $history->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            More Snapshot - {{ $history->employee->full_name ?? 'Employee' }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- 🔹 Card Wrapper -->
                                                        <div class="card shadow-sm border-0">
                                                            <div class="card-header bg-light text-primary">
                                                                Extra Salary Details
                                                            </div>
                                                            <div class="card-body p-0">
                                                                <table class="table table-hover snapshot-table mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th style="width:50%">Field</th>
                                                                            <th>Value</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($hiddenRows as $key => $value)
                                                                            <tr>
                                                                                <td class="fw-semibold text-capitalize">
                                                                                    {{ str_replace('_', ' ', $key) }}
                                                                                </td>
                                                                                <td class="text-end text-muted">
                                                                                    {{ $value }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <!-- 🔹 End Card -->
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#salaryHistoryTable').DataTable({
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, 100, 200], [5, 10, 25, 50, 100, 200]],
                searching: true,
                ordering: false,
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search employee..."
                }
            });
        });
    </script>
@endsection
