@extends('layouts.admin')
@section('content')
<div class="row">
    <!-- Report Details -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Performance Report - {{ $performanceReport->employee->full_name ?? 'N/A' }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Date</th><td>{{ $performanceReport->date }}</td>
                            <th>Employee</th><td>{{ $performanceReport->employee->full_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Sales</th><td>{{ $performanceReport->sales }}</td>
                            <th>Cost of Sell</th><td>{{ $performanceReport->cost_of_sell }}</td>
                        </tr>
                        <tr>
                            <th>Material Cost</th><td>{{ $performanceReport->metrial_cost }}</td>
                            <th>Salaries</th><td>{{ $performanceReport->salaries }}</td>
                        </tr>
                        <tr>
                            <th>Tour & Travel</th><td>{{ $performanceReport->tour_travel }}</td>
                            <th>Other Cost</th><td>{{ $performanceReport->other_cost }}</td>
                        </tr>
                        <tr>
                            <th>Net Profit</th><td>{{ $performanceReport->net_profit }}</td>
                            <th>Profit Points</th><td>{{ $performanceReport->profit_points }}</td>
                        </tr>
                        <tr>
                            <th>Half Profit Points</th><td>{{ $performanceReport->half_profit_points }}</td>
                            <th>Unpaid Amount</th><td>{{ $performanceReport->unpaid_amount }}</td>
                        </tr>
                        <tr>
                            <th>Unpaid Points</th><td>{{ $performanceReport->unpaid_points }}</td>
                            <th>Half Unpaid Points</th><td>{{ $performanceReport->half_unpaid_points }}</td>
                        </tr>
                        <tr>
                            <th>Final Points</th><td>{{ $performanceReport->final_points }}</td>
                            <th>Performance Status</th><td>{{ $performanceReport->performance_status }}</td>
                        </tr>
                        <tr>
                            <th>Status</th><td>{{ ucfirst($performanceReport->status) }}</td>
                            <th>Attachment</th>
                            <td>
                                @if($performanceReport->attachment)
                                    @php $ext = pathinfo($performanceReport->attachment, PATHINFO_EXTENSION); @endphp
                                    @if(in_array($ext, ['pdf']))
                                        <iframe src="{{ asset('storage/' . $performanceReport->attachment) }}" width="100%" height="200px"></iframe>
                                    @elseif(in_array($ext, ['jpg','jpeg','png','gif']))
                                        <img src="{{ asset('storage/' . $performanceReport->attachment) }}" class="img-fluid">
                                    @else
                                        <a href="{{ asset('storage/' . $performanceReport->attachment) }}" target="_blank" class="btn btn-sm btn-info">Open File</a>
                                    @endif
                                @else
                                    No attachment
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Performance Meter -->
    <div class="col-md-6">
        <div class="card mb-4 text-center">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Performance Meter</h5>
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="toggleUnpaid" checked>
                    <label class="form-check-label" for="toggleUnpaid">Include Unpaid / Credit Amount</label>
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
    </div>
</div>
@endsection
@section('scripts')
<script>
const profitPoints = {{ $performanceReport->profit_points ?? 0 }};
const finalPoints = {{ $performanceReport->final_points ?? 0 }};
const maxScore = 2000;
const minScore = -500; // allow negative
let currentScore = 0;
let targetScore = finalPoints;

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
    {text:"OUTSTANDING", color:"#8000ff", min:2001, max:3000},
];

function getStatus(score){
    for(const r of ranges){
        if(score >= r.min && score <= r.max) return r;
    }
    return ranges[0];
}

function drawGauge(score){
    ctx.clearRect(0,0,canvas.width, canvas.height);

    // --- draw colored arcs ---
    let totalRange = maxScore - minScore;
    let startAngle = Math.PI; // left point
    for(const r of ranges){
        let rMin = Math.max(r.min, minScore);
        let rMax = Math.min(r.max, maxScore);
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

    // --- clamp score for drawing ---
    if(score < minScore) score = minScore;
    if(score > maxScore) score = maxScore;

    // --- draw needle ---
    const angle = Math.PI + ((score - minScore) / (maxScore - minScore)) * Math.PI;
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

    // --- center circle ---
    ctx.beginPath();
    ctx.arc(centerX, centerY, 10, 0, Math.PI*2);
    ctx.fillStyle = "#000";
    ctx.fill();
}

function animateNeedle(){
    if(Math.abs(targetScore - currentScore) < 1){
        currentScore = targetScore;
        drawGauge(currentScore);
        return;
    }
    currentScore += (targetScore - currentScore) * 0.1; // smooth animation
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

// initial draw
updateMeter(targetScore);

// checkbox toggle
document.getElementById('toggleUnpaid').addEventListener('change', function(){
    updateMeter(this.checked ? finalPoints : profitPoints);
});
</script>

@endsection
