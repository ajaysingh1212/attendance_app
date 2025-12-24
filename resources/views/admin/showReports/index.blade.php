@extends('layouts.admin')
@section('content')

{{-- Filter Form --}}
<div class="card mb-3">
    <div class="card-header">Filter Performance Report</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.show-reports.index') }}" class="form-inline">
            <div class="form-group mr-2">
                <label for="employee_id" class="mr-2">Employee</label>
                <select name="employee_id" id="employee_id" class="form-control select2">
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $selectedEmployee == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mr-2">
                <label for="months" class="mr-2">Select Months</label>
                <select name="months[]" id="months" class="form-control select2" multiple>
                    @php
                        $currentYear = now()->year;
                        $startYear = $currentYear - 2; 
                        $monthsList = [];
                        for ($y = $startYear; $y <= $currentYear; $y++) {
                            for ($m = 1; $m <= 12; $m++) {
                                $monthsList[] = \Carbon\Carbon::create($y, $m, 1);
                            }
                        }
                    @endphp
                    @foreach($monthsList as $month)
                        <option value="{{ $month->format('Y-m') }}" {{ collect($selectedMonths)->contains($month->format('Y-m')) ? 'selected' : '' }}>
                            {{ $month->format('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>
</div>

{{-- Performance Meter --}}
@if($performanceReports->count())
<div class="card mt-4">
    <div class="card-header">Performance Meter</div>
    <div class="card-body text-center">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="toggleCredit" checked>
            <label class="form-check-label" for="toggleCredit">Include Credit Amount</label>
        </div>

        <canvas id="gaugeCanvas" width="300" height="200"></canvas>

        <div class="d-flex justify-content-between w-100 mt-2">
            <span style="color:#800000;font-size:12px;">Very Negative<br>-500 – -1</span>
            <span style="color:#ff0000;font-size:12px;">Poor<br>0 – 500</span>
            <span style="color:#ff6600;font-size:12px;">Fair<br>501 – 800</span>
            <span style="color:#ffcc00;font-size:12px;">Good<br>801 – 1200</span>
            <span style="color:#33cc33;font-size:12px;">Very Good<br>1201 – 1500</span>
            <span style="color:#0099ff;font-size:12px;">Excellent<br>1501 – 2000</span>
            <span style="color:#8000ff;font-size:12px;">Outstanding<br>2000+</span>
        </div>

        <h6 class="mt-2" id="meterScore">Score: 0</h6>
        <h6 id="meterStatus" class="fw-bold mt-1">--</h6>
    </div>
</div>

{{-- Monthly Breakdown Table --}}
<div class="card mt-4">
    <div class="card-header">Monthly Breakdown</div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Profit Points</th> {{-- final_points दिखेगा --}}
                    <th>Credit Points</th>
                    <th>Final (Profit + Credit)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyData as $month => $data)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</td>
                        <td>{{ $data['final_points'] }}</td> {{-- final_points use --}}
                        <td>{{ $data['total_credit'] }}</td>
                        <td>{{ $data['final'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
const monthlyData = @json($monthlyData);
const maxScore = 3000;
const minScore = -500;
let currentScore = 0;

// Profit = final_points
function getProfitAverage() {
    let total = 0, count = 0;
    for(const m in monthlyData){ total += monthlyData[m].final_points; count++; }
    return count > 0 ? Math.round(total / count) : 0;
}

// Final = final_points + total_credit
function getFinalAverage() {
    let total = 0, count = 0;
    for(const m in monthlyData){ total += (monthlyData[m].final_points + monthlyData[m].total_credit); count++; }
    return count > 0 ? Math.round(total / count) : 0;
}

let targetScore = getFinalAverage();

const canvas = document.getElementById('gaugeCanvas');
const ctx = canvas.getContext('2d');
const centerX = canvas.width / 2;
const centerY = canvas.height - 10;
const radius = 120;

const ranges = [
    {text:"VERY NEGATIVE", color:"#800000", min:-500, max:-1},
    {text:"POOR", color:"#ff0000", min:0, max:500},
    {text:"FAIR", color:"#ff6600", min:501, max:800},
    {text:"GOOD", color:"#ffcc00", min:801, max:1200},
    {text:"VERY GOOD", color:"#33cc33", min:1201, max:1500},
    {text:"EXCELLENT", color:"#0099ff", min:1501, max:2000},
    {text:"OUTSTANDING", color:"#8000ff", min:2001, max:Infinity},
];

function getStatus(score){
    for(const r of ranges){
        if(score >= r.min && (r.max === Infinity || score <= r.max)) return r;
    }
    return ranges[0];
}

function drawGauge(score){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    let startAngle = Math.PI;
    let totalRange = maxScore - minScore;
    for(const r of ranges){
        let rMin = Math.max(r.min, minScore);
        let rMax = Math.min(r.max === Infinity ? maxScore : r.max, maxScore);
        if(rMax <= rMin) continue;
        let portion = (rMax - rMin) / totalRange;
        let endAngle = startAngle + Math.PI * portion;
        ctx.beginPath();
        ctx.strokeStyle = r.color;
        ctx.lineWidth = 20;
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.stroke();
        startAngle = endAngle;
    }

    let clampedScore = Math.min(Math.max(score, minScore), maxScore);
    const angle = Math.PI + ((clampedScore - minScore) / (maxScore - minScore)) * Math.PI;
    ctx.save();
    ctx.translate(centerX, centerY);
    ctx.rotate(angle);
    ctx.beginPath();
    ctx.moveTo(0,-5);
    ctx.lineTo(radius-10,0);
    ctx.lineTo(0,5);
    ctx.fillStyle = "#000";
    ctx.fill();
    ctx.restore();

    ctx.beginPath();
    ctx.arc(centerX, centerY, 10, 0, Math.PI*2);
    ctx.fillStyle = "#000";
    ctx.fill();
}

function animateNeedle(){
    if(Math.abs(targetScore - currentScore) < 1){ currentScore = targetScore; drawGauge(currentScore); return; }
    currentScore += (targetScore - currentScore) * 0.1;
    drawGauge(currentScore);
    requestAnimationFrame(animateNeedle);
}

function updateMeter(newScore){
    targetScore = newScore;
    animateNeedle();
    const status = getStatus(newScore);
    document.getElementById('meterScore').innerText = "Score: " + newScore.toFixed(2);
    document.getElementById('meterStatus').innerText = status.text;
    document.getElementById('meterStatus').style.color = status.color;
}

updateMeter(targetScore);

document.getElementById('toggleCredit').addEventListener('change', function(){
    updateMeter(this.checked ? getFinalAverage() : getProfitAverage());
});
</script>
@endif
@endsection
