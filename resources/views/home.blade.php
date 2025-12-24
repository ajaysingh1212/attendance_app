@extends('layouts.admin')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="content">
    <!-- User Dropdown -->
    <form method="GET" action="{{ route('admin.home') }}" id="userForm">
        <div class="form-group">
            <label for="user_id"><strong>Select User:</strong></label>
            <select name="user_id" id="user_id" class="form-control">
                <option value="">-- Select User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (isset($selectedUserId) && $selectedUserId == $user->id) ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            @if(request()->has('month'))
                <input type="hidden" name="month" value="{{ request()->get('month') }}">
            @endif
        </div>
    </form>

    @if(!empty($monthlyData))
        @php
            $statusCount = [
                'present' => 0, 'absent' => 0, 'half_day' => 0, 'leave' => 0,
                'week_off' => 0, 'holiday' => 0, 'late' => 0, 'no_data' => 0
            ];
            foreach ($monthlyData as $status) {
                $matched = false;
                foreach ($statusCount as $key => $count) {
                    if (str_contains($status, $key)) {
                        $statusCount[$key]++;
                        $matched = true;
                    }
                }
                if (!$matched) $statusCount['no_data']++;
            }
            $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthInput);
            $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
            $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        @endphp

        <!-- Month Navigation -->
        <div class="d-flex justify-content-between align-items-center my-3">
            <a href="{{ route('admin.home', ['user_id' => $selectedUserId, 'month' => $previousMonth]) }}" class="btn btn-outline-primary">
                &laquo; {{ \Carbon\Carbon::createFromFormat('Y-m', $previousMonth)->format('F Y') }}
            </a>
            <h4 class="text-center mb-0">{{ $currentMonth->format('F Y') }}</h4>
            <a href="{{ route('admin.home', ['user_id' => $selectedUserId, 'month' => $nextMonth]) }}" class="btn btn-outline-primary">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $nextMonth)->format('F Y') }} &raquo;
            </a>
        </div>

        <!-- Download PDF Button -->
        <div class="text-end mb-3">
            <button id="downloadPdfBtn" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf"></i> Download PDF
            </button>
        </div>

        <!-- Printable Section Start -->
        <div id="printableArea">
            <!-- Status Summary -->
            <div class="row text-center my-3">
                @foreach($statusCount as $status => $count)
                    <div class="col">
                        <div class="p-2 rounded 
                            @if($status == 'present') bg-success text-white
                            @elseif($status == 'absent') bg-danger text-white
                            @elseif($status == 'half_day') bg-warning text-dark
                            @elseif($status == 'leave') bg-primary text-white
                            @elseif($status == 'week_off') bg-secondary text-white
                            @elseif($status == 'holiday') bg-info text-white
                            @elseif($status == 'late') bg-warning text-white
                            @else bg-light text-dark
                            @endif">
                            <strong>{{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Monthly Calendar -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Monthly Attendance Calendar</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-3">
                        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                            <div class="text-center fw-bold" style="width: 100px;">{{ $day }}</div>
                        @endforeach
                    </div>

                    @php
                        $firstDate = \Carbon\Carbon::createFromFormat('Y-m-d', array_key_first($monthlyData));
                        $dates = [];
                        foreach ($monthlyData as $date => $status) {
                            $dates[] = ['date' => $date, 'status' => $status];
                        }
                        $paddingStart = $firstDate->dayOfWeek;
                        for ($i = 0; $i < $paddingStart; $i++) array_unshift($dates, null);
                        while (count($dates) % 7 !== 0) $dates[] = null;
                        $calendarGrid = array_chunk($dates, 7);
                    @endphp

                    <div class="calendar-grid d-flex flex-column gap-2">
                        @foreach($calendarGrid as $week)
                            <div class="d-flex justify-content-center">
                                @foreach($week as $day)
                                    @if(is_null($day))
                                        <div class="m-1 p-2 bg-light border" style="width: 100px; height: 80px;"></div>
                                    @else
                                        @php
                                            $color = match(true) {
                                                str_contains($day['status'], 'absent') => 'bg-danger text-white',
                                                str_contains($day['status'], 'half_day') => 'bg-warning text-dark',
                                                str_contains($day['status'], 'present') => 'bg-success text-white',
                                                str_contains($day['status'], 'leave') => 'bg-primary text-white',
                                                str_contains($day['status'], 'week_off') => 'bg-secondary text-white',
                                                str_contains($day['status'], 'holiday') => 'bg-info text-white',
                                                str_contains($day['status'], 'late') => 'bg-warning text-white',
                                                default => 'bg-dark text-white',
                                            };
                                        @endphp
                                        <a href="{{ route('admin.home', ['user_id' => $selectedUserId, 'date' => $day['date'], 'month' => $monthInput]) }}"
                                           class="m-1 p-2 text-center rounded shadow-sm {{ $color }}"
                                           style="width: 100px; height: 80px; text-decoration: none; display: flex; flex-direction: column; justify-content: center;">
                                            <strong>{{ \Carbon\Carbon::parse($day['date'])->format('d M') }}</strong>
                                            <small>{{ ucfirst($day['status'] ?? 'No Data') }}</small>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!-- Printable Section End -->
    @endif

   @if($attendances && $attendances->count())
    @foreach($attendances as $record)
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Attendance Details</h5>
                    <small>
                        User: <strong>{{ $record->user->name }}</strong> |
                        Date: <strong>{{ \Carbon\Carbon::parse($record->punch_in_time)->format('d M Y') }}</strong>
                    </small>
                </div>
                <button type="button"
                        class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        data-date="{{ \Carbon\Carbon::parse($record->punch_in_time)->format('Y-m-d') }}"
                        data-time="{{ \Carbon\Carbon::parse($record->punch_in_time)->format('H:i') }}">
                    Update Status
                </button>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <!-- Punch In -->
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <strong>Punch In Location</strong>
                            </div>
                            <div class="card-body p-2">
                                @if($record->punch_in_latitude && $record->punch_in_longitude)
                                    <iframe width="100%" height="300" frameborder="0"
                                            src="https://maps.google.com/maps?q={{ $record->punch_in_latitude }},{{ $record->punch_in_longitude }}&t=k&z=16&output=embed"
                                            allowfullscreen></iframe>
                                    <p class="mt-2"><strong>Location:</strong> {{ $record->punch_in_location }}</p>
                                    <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($record->punch_in_time)->format('h:i A') }}</p>
                                @else
                                    <p class="text-danger">Punch In location not available.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Punch Out -->
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <strong>Punch Out Location</strong>
                            </div>
                            <div class="card-body p-2">
                                @if($record->punch_out_latitude && $record->punch_out_longitude)
                                    <iframe width="100%" height="300" frameborder="0"
                                            src="https://maps.google.com/maps?q={{ $record->punch_out_latitude }},{{ $record->punch_out_longitude }}&t=k&z=16&output=embed"
                                            allowfullscreen></iframe>
                                    <p class="mt-2"><strong>Location:</strong> {{ $record->punch_out_location }}</p>
                                    <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($record->punch_out_time)->format('h:i A') }}</p>
                                @else
                                    <p class="text-danger">Punch Out location not available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Attendance Details</h5>
            @if(isset($selectedDate))
                <button type="button"
                        class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        data-date="{{ \Carbon\Carbon::parse($selectedDate)->format('Y-m-d') }}"
                        data-time="09:00"> {{-- Default time or empty --}}
                    Update Status
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">No attendance records found for the selected criteria.</div>
        </div>
    </div>
@endif


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Attendance Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="statusForm" method="POST" action="{{ route('admin.attendance.save') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="modalUserId" value="{{ $selectedUserId }}">
                        <input type="hidden" name="date" id="modalDate">
                        <input type="hidden" name="time" id="modalTime">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="absent">Absent</option>
                                <option value="present">Present</option>
                                <option value="half_day">Half Day</option>
                                <option value="leave">Leave</option>
                                <option value="week_off">Week Off</option>
                                <option value="holiday">Holiday</option>
                                <option value="late">Late</option>
                                <option value="paid_leave">Paid Leave</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <p><strong>Date:</strong> <span id="displayDate"></span></p>
                            <p><strong>Time:</strong> <span id="displayTime"></span></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('#user_id').select2({
                placeholder: "-- Select User --",
                width: '100%'
            }).on('change', function () {
                $('#userForm').submit();
            });

            // PDF Download
            $('#downloadPdfBtn').on('click', function () {
                const element = document.getElementById('printableArea');
                const opt = {
                    margin: 0.5,
                    filename: 'attendance_{{ $selectedUserId ?? "user" }}_{{ $monthInput }}.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
                };
                html2pdf().set(opt).from(element).save();
            });

            // Status Modal Handling
            $('#statusModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const date = button.data('date');
                const time = button.data('time');
                
                $('#modalDate').val(date);
                $('#modalTime').val(time);
                $('#displayDate').text(date);
                $('#displayTime').text(time);
            });

            // Form Submission
            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#statusModal').modal('hide');
                            location.reload();
                        } else {
                            alert(response.message || 'Error saving attendance');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection