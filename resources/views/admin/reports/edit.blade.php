@extends('layouts.admin')
@section('content')

<div class="container-fluid">
    <div class="card shadow-lg border-0 rounded-lg mt-4">
        <div class="card-header bg-primary text-white text-center">
            <h3 class="mb-0">
                <i class="fas fa-edit"></i> Edit Performance Report
            </h3>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.performance-reports.update', $performanceReport->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Employee & Month-Year -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="employee_id">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $performanceReport->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="month_year">Month & Year</label>
                        <input class="form-control" type="month" name="date" id="month_year"
                               value="{{ old('month_year', $performanceReport->date ? \Carbon\Carbon::parse($performanceReport->date)->format('Y-m') : '') }}" required>
                    </div>
                </div>

                <!-- Sales & Salaries -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="sales">Sales Amount</label>
                        <input class="form-control" type="number" name="sales" id="sales" value="{{ old('sales', $performanceReport->sales) }}" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="salaries">Salaries / Month</label>
                        <input class="form-control" type="number" name="salaries" id="salaries" value="{{ old('salaries', $performanceReport->salaries) }}" required>
                    </div>
                </div>

                <!-- Material, Tour & Other Costs -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="metrial_cost">Material Cost</label>
                        <input class="form-control" type="number" name="metrial_cost" id="metrial_cost" value="{{ old('metrial_cost', $performanceReport->metrial_cost) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="tour_travel">Tour & Travel</label>
                        <input class="form-control" type="number" name="tour_travel" id="tour_travel" value="{{ old('tour_travel', $performanceReport->tour_travel) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="other_cost">Other Costs</label>
                        <input class="form-control" type="number" name="other_cost" id="other_cost" value="{{ old('other_cost', $performanceReport->other_cost) }}">
                    </div>
                </div>

                <!-- Cost of Sell & Net Profit -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="cost_of_sell">Total Cost of Sell</label>
                        <input class="form-control bg-light fw-bold" type="text" name="cost_of_sell" id="cost_of_sell" value="{{ old('cost_of_sell', $performanceReport->cost_of_sell) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="net_profit">Net Profit</label>
                        <input class="form-control bg-success text-white fw-bold" type="text" id="net_profit" name="net_profit" value="{{ old('net_profit', $performanceReport->net_profit) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="profit_percentage">Profit Percentage (%)</label>
                        <input class="form-control bg-info text-white fw-bold" type="text" id="profit_percentage" name="profit_percentage" value="{{ old('profit_percentage', $performanceReport->profit_percentage) }}" readonly>
                    </div>
                </div>

                <!-- Profit Points -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="profit_points">Profit Points</label>
                        <input class="form-control bg-warning fw-bold" type="text" id="profit_points" name="profit_points" value="{{ old('profit_points', $performanceReport->profit_points) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="half_profit_percentage">Half Profit Percentage (%)</label>
                        <input class="form-control bg-light fw-bold" type="text" id="half_profit_percentage" name="half_profit_percentage" value="{{ old('half_profit_percentage', $performanceReport->half_profit_percentage) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="half_profit_points">Half Profit Points</label>
                        <input class="form-control bg-light fw-bold" type="text" id="half_profit_points" name="half_profit_points" value="{{ old('half_profit_points', $performanceReport->half_profit_points) }}" readonly>
                    </div>
                </div>

                <!-- Unpaid Calculations -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="unpaid_amount">Unpaid / Credit Amount</label>
                        <input class="form-control" type="number" name="unpaid_amount" id="unpaid_amount" value="{{ old('unpaid_amount', $performanceReport->unpaid_amount) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="unpaid_percentage">Unpaid Percentage (%)</label>
                        <input class="form-control bg-danger text-white fw-bold" type="text" id="unpaid_percentage" name="unpaid_percentage" value="{{ old('unpaid_percentage', $performanceReport->unpaid_percentage) }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="unpaid_points">Unpaid Points</label>
                        <input class="form-control bg-warning fw-bold" type="text" id="unpaid_points" name="unpaid_points" value="{{ old('unpaid_points', $performanceReport->unpaid_points) }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="half_unpaid_percentage">Half Unpaid Percentage (%)</label>
                        <input class="form-control bg-light fw-bold" type="text" id="half_unpaid_percentage" name="half_unpaid_percentage" value="{{ old('half_unpaid_percentage', $performanceReport->half_unpaid_percentage) }}" readonly>
                    </div>
                </div>

                <!-- Half Unpaid Points -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="half_unpaid_points">Half Unpaid Points</label>
                        <input class="form-control bg-light fw-bold" type="text" id="half_unpaid_points" name="half_unpaid_points" value="{{ old('half_unpaid_points', $performanceReport->half_unpaid_points) }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="final_points">Final Points</label>
                        <input class="form-control bg-primary text-white fw-bold" type="text" id="final_points" name="final_points" value="{{ old('final_points', $performanceReport->final_points) }}" readonly>
                    </div>
                </div>

                <!-- Performance Status -->
                <div class="card mt-4 shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Performance Status</h5>
                    </div>
                    <div class="card-body text-center">
                        <h3 id="performance_status_label" class="fw-bold text-uppercase">
                            {{ old('performance_status', $performanceReport->performance_status ?? '--') }}
                        </h3>
                        <input type="hidden" id="performance_status" name="performance_status" value="{{ old('performance_status', $performanceReport->performance_status) }}">
                    </div>
                </div>

                <!-- Report Status -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="status">Report Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Pending" {{ old('status', $performanceReport->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ old('status', $performanceReport->status) == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Decline" {{ old('status', $performanceReport->status) == 'Decline' ? 'selected' : '' }}>Decline</option>
                        </select>
                    </div>
                </div>

                <!-- Attachment -->
                <div class="form-group mt-4">
                    <label for="attachment">Attachment (PDF, Excel, Image, PPT, etc.)</label>
                    <input type="file" class="form-control" name="attachment" id="attachment" accept=".pdf,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                    @if($performanceReport->attachment)
                        <p class="mt-2">Current File: 
                            <a href="{{ asset($performanceReport->attachment) }}" target="_blank" class="btn btn-sm btn-info">View</a>
                        </p>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="form-group text-center mt-4">
                    <button class="btn btn-lg btn-success px-5" type="submit">
                        <i class="fas fa-save"></i> Update Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const material = document.getElementById('metrial_cost');
    const salaries = document.getElementById('salaries');
    const tourTravel = document.getElementById('tour_travel');
    const otherCost = document.getElementById('other_cost');
    const costOfSell = document.getElementById('cost_of_sell');
    const sales = document.getElementById('sales');
    const netProfit = document.getElementById('net_profit');
    const profitPercentage = document.getElementById('profit_percentage');
    const profitPoints = document.getElementById('profit_points');
    const halfProfitPercentage = document.getElementById('half_profit_percentage');
    const halfProfitPoints = document.getElementById('half_profit_points');
    const unpaidAmount = document.getElementById('unpaid_amount');
    const unpaidPercentage = document.getElementById('unpaid_percentage');
    const unpaidPoints = document.getElementById('unpaid_points');
    const halfUnpaidPercentage = document.getElementById('half_unpaid_percentage');
    const halfUnpaidPoints = document.getElementById('half_unpaid_points');
    const finalPoints = document.getElementById('final_points');
    const performanceStatusInput = document.getElementById('performance_status');
    const performanceStatusLabel = document.getElementById('performance_status_label');

    function calculateAll() {
        const materialVal = parseFloat(material.value) || 0;
        const salariesVal = parseFloat(salaries.value) || 0;
        const tourVal = parseFloat(tourTravel.value) || 0;
        const otherVal = parseFloat(otherCost.value) || 0;
        const salesVal = parseFloat(sales.value) || 0;
        const unpaidVal = parseFloat(unpaidAmount.value) || 0;

        // Cost of Sell Calculation
        const totalCost = materialVal + salariesVal + tourVal + otherVal;
        costOfSell.value = totalCost.toFixed(2);

        // Net Profit Calculation
        const profit = salesVal - totalCost;
        netProfit.value = profit.toFixed(2);

        // Profit Percentage & Points
        const profitPercent = salesVal > 0 ? (profit / salesVal) * 100 : 0;
        profitPercentage.value = profitPercent.toFixed(2);
        profitPoints.value = (profitPercent * 100).toFixed(2);

        // Half Profit Percentage & Points
        halfProfitPercentage.value = (profitPercent / 2).toFixed(2);
        halfProfitPoints.value = ((profitPercent * 100) / 2).toFixed(2);

        // Unpaid Percentage & Points
        const unpaidPercent = salesVal > 0 ? (unpaidVal / salesVal) * 100 : 0;
        unpaidPercentage.value = unpaidPercent.toFixed(2);
        unpaidPoints.value = (unpaidPercent * 100).toFixed(2);

        // Half Unpaid Percentage & Points
        halfUnpaidPercentage.value = (unpaidPercent / 2).toFixed(2);
        halfUnpaidPoints.value = ((unpaidPercent * 100) / 2).toFixed(2);

        // Final Points Calculation
        const finalPts = (parseFloat(profitPoints.value) || 0) - (parseFloat(halfUnpaidPoints.value) || 0);
        finalPoints.value = finalPts.toFixed(2);

        // Status Update
        let status = "";
        let color = "black";
        if (finalPts < 100) {
            status = "NEGATIVE VERY POOR";
            color = "red";
        } else if (finalPts >= 101 && finalPts <= 500) {
            status = "FAIL";
            color = "#ff6600";
        } else if (finalPts >= 501 && finalPts <= 800) {
            status = "AVERAGE";
            color = "#ffcc00";
        } else if (finalPts >= 801 && finalPts <= 1200) {
            status = "GOOD";
            color = "#33cc33";
        } else if (finalPts >= 1201 && finalPts <= 1500) {
            status = "EXCELLENT";
            color = "#0099ff";
        } else if (finalPts > 1500) {
            status = "TOP CLASS";
            color = "#8000ff";
        }

        performanceStatusLabel.textContent = status;
        performanceStatusLabel.style.color = color;
        performanceStatusInput.value = status;
    }

    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', calculateAll);
    });

    calculateAll();
});
</script>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const material = document.getElementById('metrial_cost');
    const salaries = document.getElementById('salaries');
    const tourTravel = document.getElementById('tour_travel');
    const otherCost = document.getElementById('other_cost');
    const costOfSell = document.getElementById('cost_of_sell');
    const sales = document.getElementById('sales');
    const netProfit = document.getElementById('net_profit');
    const profitPercentage = document.getElementById('profit_percentage');
    const profitPoints = document.getElementById('profit_points');
    const halfProfitPercentage = document.getElementById('half_profit_percentage');
    const halfProfitPoints = document.getElementById('half_profit_points');
    const unpaidAmount = document.getElementById('unpaid_amount');
    const unpaidPercentage = document.getElementById('unpaid_percentage');
    const unpaidPoints = document.getElementById('unpaid_points');
    const halfUnpaidPercentage = document.getElementById('half_unpaid_percentage');
    const halfUnpaidPoints = document.getElementById('half_unpaid_points');
    const finalPoints = document.getElementById('final_points');
    const performanceStatusInput = document.getElementById('performance_status');
    const performanceStatusLabel = document.getElementById('performance_status_label');

    function calculateAll() {
        const materialVal = parseFloat(material.value) || 0;
        const salariesVal = parseFloat(salaries.value) || 0;
        const tourVal = parseFloat(tourTravel.value) || 0;
        const otherVal = parseFloat(otherCost.value) || 0;
        const salesVal = parseFloat(sales.value) || 0;
        const unpaidVal = parseFloat(unpaidAmount.value) || 0;

        // Cost of Sell Calculation
        const totalCost = materialVal + salariesVal + tourVal + otherVal;
        costOfSell.value = totalCost.toFixed(2);

        // Net Profit Calculation
        const profit = salesVal - totalCost;
        netProfit.value = profit.toFixed(2);

        // Profit Percentage & Points
        const profitPercent = salesVal > 0 ? (profit / salesVal) * 100 : 0;
        profitPercentage.value = profitPercent.toFixed(2);
        profitPoints.value = (profitPercent * 100).toFixed(2);

        // Half Profit Percentage & Points
        halfProfitPercentage.value = (profitPercent / 2).toFixed(2);
        halfProfitPoints.value = ((profitPercent * 100) / 2).toFixed(2);

        // Unpaid Percentage & Points
        const unpaidPercent = salesVal > 0 ? (unpaidVal / salesVal) * 100 : 0;
        unpaidPercentage.value = unpaidPercent.toFixed(2);
        unpaidPoints.value = (unpaidPercent * 100).toFixed(2);

        // Half Unpaid Percentage & Points
        halfUnpaidPercentage.value = (unpaidPercent / 2).toFixed(2);
        halfUnpaidPoints.value = ((unpaidPercent * 100) / 2).toFixed(2);

        // Final Points Calculation
        const finalPts = (parseFloat(profitPoints.value) || 0) - (parseFloat(halfUnpaidPoints.value) || 0);
        finalPoints.value = finalPts.toFixed(2);

        // Status Update
        let status = "";
        let color = "black";
        if (finalPts < 100) {
            status = "NEGATIVE VERY POOR";
            color = "red";
        } else if (finalPts >= 101 && finalPts <= 500) {
            status = "FAIL";
            color = "#ff6600";
        } else if (finalPts >= 501 && finalPts <= 800) {
            status = "AVERAGE";
            color = "#ffcc00";
        } else if (finalPts >= 801 && finalPts <= 1200) {
            status = "GOOD";
            color = "#33cc33";
        } else if (finalPts >= 1201 && finalPts <= 1500) {
            status = "EXCELLENT";
            color = "#0099ff";
        } else if (finalPts > 1500) {
            status = "TOP CLASS";
            color = "#8000ff";
        }

        performanceStatusLabel.textContent = status;
        performanceStatusLabel.style.color = color;
        performanceStatusInput.value = status;
    }

    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', calculateAll);
    });

    calculateAll();
});
</script>
@endsection
