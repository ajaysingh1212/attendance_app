@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-dark text-white">
        <strong>Expense & Income Report</strong>
    </div>

    <div class="card-body">
        {{-- FILTERS --}}
        <form method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-lg-3">
                    <label><strong>User</strong></label>
                  <select name="employee_id" class="form-control">
    <option value="">All Employees</option>
    @foreach($employees as $employee)
        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
            {{ $employee->full_name }} ({{ $employee->employee_code ?? 'No Code' }})
        </option>
    @endforeach
</select>

                </div>

                <div class="col-lg-3">
                    <label><strong>Filter Type</strong></label>
                    <select name="filter_type" id="filter_type" class="form-control">
                        <option value="">All</option>
                        <option value="day" {{ request('filter_type') == 'day' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('filter_type') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('filter_type') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="half_year" {{ request('filter_type') == 'half_year' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="year" {{ request('filter_type') == 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('filter_type') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div class="col-lg-3 custom-date" style="display:none;">
                    <label><strong>From Date</strong></label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>

                <div class="col-lg-3 custom-date" style="display:none;">
                    <label><strong>To Date</strong></label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <div class="col-lg-12 text-end mt-2">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </div>
        </form>

        {{-- METRIC CARDS --}}
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card text-white bg-success shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Income</h5>
                        <p class="h4">₹{{ number_format($totalIncome, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card text-white bg-danger shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Expenses</h5>
                        <p class="h4">₹{{ number_format($totalExpense, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card text-white bg-info shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Remaining Profit</h5>
                        <p class="h4">₹{{ number_format($profit, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- INCOME TABLE --}}
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card border-success shadow-sm">
                    <div class="card-header bg-light font-weight-bold">Income Breakdown</div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupedIncome as $category => $amount)
                                    <tr>
                                        <td>{{ $category ?? 'Uncategorized' }}</td>
                                        <td class="text-end">{{ number_format($amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2">No income data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- EXPENSE TABLE --}}
            <div class="col-lg-6">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-light font-weight-bold">Expense Breakdown</div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupedExpenses as $desc => $amount)
                                    <tr>
                                        <td>{{ $desc ?? 'Uncategorized' }}</td>
                                        <td class="text-end">{{ number_format($amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2">No expense data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterType = document.getElementById('filter_type');
        const customDateFields = document.querySelectorAll('.custom-date');

        function toggleCustomDates() {
            const isCustom = filterType.value === 'custom';
            customDateFields.forEach(field => field.style.display = isCustom ? 'block' : 'none');
        }

        filterType.addEventListener('change', toggleCustomDates);
        toggleCustomDates();
    });
</script>
@endsection
