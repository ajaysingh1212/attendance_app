@extends('layouts.admin')

@section('styles')
    @include('admin.groupTasks.ui-style')
    <style>
        .accept-card  { background: linear-gradient(135deg,#fef3c7,#fffbeb); border: 1.5px solid #fde68a; }
        .complete-card{ background: linear-gradient(135deg,#ecfdf5,#f0fdf4); border: 1.5px solid #a7f3d0; }
    </style>
@endsection

@section('content')
<div class="gt-page">

    {{-- Hero --}}
    <div class="gt-hero">
        <div class="gt-hero-text">
            <h3 class="gt-title">{{ $groupTask->title }}</h3>
            <div class="gt-subtitle">
                {{ optional($groupTask->group)->name }} &nbsp;·&nbsp;
                Created by {{ optional($groupTask->createdBy)->name }} &nbsp;·&nbsp;
                {{ optional($groupTask->created_at)->format('d M Y') }}
            </div>
        </div>
        <div class="gt-actions">
            @if($groupTask->status !== 'completed')
            <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.edit', $groupTask) }}">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.index') }}">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="border-radius:10px;">{{ session('success') }}</div>
    @endif

    <div class="row">

        {{-- ── LEFT: Detail + Timeline ── --}}
        <div class="col-lg-8">

            {{-- Overview --}}
            <div class="td-section">
                <div class="td-section-title">📄 Task Overview</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small style="color:#94a3b8;font-weight:600;text-transform:uppercase;font-size:.7rem;">Status</small>
                        <div class="mt-1">
                            <span class="gt-badge gt-badge-{{ $groupTask->status }}" style="font-size:.82rem;">
                                {{ ucfirst($groupTask->status) }}
                            </span>
                            @if($groupTask->is_delayed)
                            <span class="gt-badge gt-badge-delayed ml-1">⚠ Delayed</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small style="color:#94a3b8;font-weight:600;text-transform:uppercase;font-size:.7rem;">Priority</small>
                        <div class="mt-1">
                            <span class="gt-badge gt-priority-{{ $groupTask->priority }}" style="font-size:.82rem;">{{ ucfirst($groupTask->priority) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small style="color:#94a3b8;font-weight:600;text-transform:uppercase;font-size:.7rem;">Due Date</small>
                        <div style="font-weight:600;font-size:.9rem;margin-top:4px;">
                            {{ optional($groupTask->due_at)->format('d M Y, h:i A') ?? '—' }}
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small style="color:#94a3b8;font-weight:600;text-transform:uppercase;font-size:.7rem;">Group</small>
                        <div style="font-weight:600;font-size:.9rem;margin-top:4px;">
                            {{ optional($groupTask->group)->name ?? '—' }}
                        </div>
                    </div>
                </div>
                @if($groupTask->description)
                <div style="background:#fff;border:1px solid var(--gt-border);border-radius:10px;padding:14px;margin-top:8px;line-height:1.7;font-size:.9rem;">
                    {!! nl2br(e($groupTask->description)) !!}
                </div>
                @endif
            </div>

            {{-- Assigned Members --}}
            <div class="td-section">
                <div class="td-section-title">👥 Assigned Members</div>
                <div>
                    @forelse($groupTask->assignees as $a)
                    <span class="member-chip" style="font-size:.83rem;padding:6px 12px 6px 6px;">
                        <span class="mc-avatar">{{ strtoupper(substr($a->name,0,2)) }}</span>
                        {{ $a->name }}
                        @if($a->pivot->status === 'accepted')
                            <span class="gt-badge gt-badge-accepted" style="font-size:.65rem;padding:2px 7px;">accepted</span>
                        @elseif($a->pivot->status === 'completed')
                            <span class="gt-badge gt-badge-completed" style="font-size:.65rem;padding:2px 7px;">completed</span>
                        @endif
                    </span>
                    @empty
                    <span style="color:#94a3b8;">No assignees.</span>
                    @endforelse
                </div>
            </div>

            {{-- Attachments --}}
            @if($groupTask->getMedia('attachments')->count() || $groupTask->getMedia('voice_notes')->count())
            <div class="td-section">
                <div class="td-section-title">📎 Task Attachments</div>
                @foreach($groupTask->getMedia('attachments') as $media)
                <a class="btn btn-sm btn-outline-primary mr-1 mb-1" target="_blank" href="{{ $media->getUrl() }}">
                    <i class="fas fa-paperclip"></i> {{ $media->file_name }}
                </a>
                @endforeach
                @foreach($groupTask->getMedia('voice_notes') as $media)
                <div class="mt-2">
                    <small style="color:#64748b;font-size:.75rem;">🎙 Voice Note</small>
                    <audio controls src="{{ $media->getUrl() }}" style="display:block;width:100%;margin-top:4px;"></audio>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Timeline --}}
            <div class="td-section">
                <div class="td-section-title">⏱ Task Timeline</div>
                <div class="gt-timeline">

                    {{-- Created --}}
                    <div class="gt-timeline-item done">
                        <div class="gt-timeline-label">📝 Task Created</div>
                        <div class="gt-timeline-meta">
                            by {{ optional($groupTask->createdBy)->name }} &nbsp;·&nbsp;
                            {{ optional($groupTask->created_at)->format('d M Y, h:i A') }}
                        </div>
                    </div>

                    {{-- Accepted --}}
                    <div class="gt-timeline-item {{ $groupTask->acceptedBy ? 'done' : ($groupTask->status === 'pending' ? 'active' : '') }}">
                        <div class="gt-timeline-label">✅ Task Accepted</div>
                        @if($groupTask->acceptedBy)
                        <div class="gt-timeline-meta">
                            by <strong>{{ $groupTask->acceptedBy->name }}</strong>
                            ({{ $groupTask->accept_role }}) &nbsp;·&nbsp;
                            {{ optional($groupTask->accepted_at)->format('d M Y, h:i A') }}
                        </div>
                        @if($groupTask->accept_narration)
                        <div class="gt-timeline-note">{{ $groupTask->accept_narration }}</div>
                        @endif
                        @if($groupTask->deadline_at && $groupTask->status === 'accepted')
                        <div style="margin-top:8px;">
                            <span class="countdown-chip ok" data-deadline="{{ $groupTask->deadline_at->toIso8601String() }}">
                                <span class="cd-dot"></span>
                                <span>Calculating…</span>
                            </span>
                        </div>
                        @endif
                        @else
                        <div class="gt-timeline-meta" style="color:#f59e0b;">Waiting for acceptance…</div>
                        @endif
                    </div>

                    {{-- Estimated deadline info --}}
                    @if($groupTask->acceptedBy)
                    <div class="gt-timeline-item {{ $groupTask->completedBy ? 'done' : 'active' }}">
                        <div class="gt-timeline-label">📅 Estimated Completion</div>
                        <div class="gt-timeline-meta">
                            @if($groupTask->estimate_type === 'hours')
                                {{ $groupTask->estimated_hours }} hour(s) from acceptance
                                @if($groupTask->deadline_at)
                                 → Deadline: {{ $groupTask->deadline_at->format('d M Y, h:i A') }}
                                @endif
                            @elseif($groupTask->estimate_type === 'date')
                                By {{ optional($groupTask->estimated_date)->format('d M Y') }}
                            @endif
                            (Requested: {{ $groupTask->requested_minutes ? round($groupTask->requested_minutes/60,1).'h' : '—' }})
                        </div>
                    </div>
                    @endif

                    {{-- Completed --}}
                    <div class="gt-timeline-item {{ $groupTask->completedBy ? 'done' : '' }}">
                        <div class="gt-timeline-label">🏁 Task Completed</div>
                        @if($groupTask->completedBy)
                        <div class="gt-timeline-meta">
                            by <strong>{{ $groupTask->completedBy->name }}</strong> &nbsp;·&nbsp;
                            {{ optional($groupTask->completed_at)->format('d M Y, h:i A') }}
                        </div>
                        @if($groupTask->completion_narration)
                        <div class="gt-timeline-note">{{ $groupTask->completion_narration }}</div>
                        @endif
                        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;">
                            <span class="gt-badge" style="background:#f1f5fb;color:#475569;">
                                ⏱ Asked: {{ $groupTask->requested_minutes ? round($groupTask->requested_minutes/60,1).'h' : '—' }}
                            </span>
                            <span class="gt-badge" style="background:#f1f5fb;color:#475569;">
                                ⏱ Actual: {{ $groupTask->actual_minutes ? round($groupTask->actual_minutes/60,1).'h' : '—' }}
                            </span>
                            @if($groupTask->delay_minutes !== null)
                            <span class="gt-badge {{ $groupTask->delay_minutes > 0 ? 'gt-badge-delayed' : 'gt-badge-completed' }}">
                                {{ $groupTask->delay_minutes > 0
                                    ? '⚠ '.round($groupTask->delay_minutes/60,1).'h late'
                                    : '✅ '.abs(round($groupTask->delay_minutes/60,1)).'h early' }}
                            </span>
                            @endif
                        </div>

                        {{-- Completion media --}}
                        @if($groupTask->getMedia('completion_attachments')->count())
                        <div style="margin-top:8px;">
                            @foreach($groupTask->getMedia('completion_attachments') as $media)
                            <a class="btn btn-sm btn-outline-success mr-1 mb-1" target="_blank" href="{{ $media->getUrl() }}">
                                <i class="fas fa-paperclip"></i> {{ $media->file_name }}
                            </a>
                            @endforeach
                        </div>
                        @endif
                        @foreach($groupTask->getMedia('completion_voice_notes') as $media)
                        <div style="margin-top:6px;">
                            <small style="color:#64748b;font-size:.75rem;">🎙 Completion Voice Note</small>
                            <audio controls src="{{ $media->getUrl() }}" style="display:block;width:100%;margin-top:4px;"></audio>
                        </div>
                        @endforeach
                        @else
                        <div class="gt-timeline-meta" style="color:#94a3b8;">Not completed yet.</div>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        {{-- ── RIGHT: Accept / Complete forms ── --}}
        <div class="col-lg-4">

            @if($groupTask->status === 'pending')
            <div class="gt-panel accept-card" style="border-radius:12px;">
                <div class="gt-panel-header" style="background:transparent;border-color:#fde68a;">
                    <span style="color:#92400e;">✋ Accept This Task</span>
                </div>
                <div class="gt-panel-body">
                    <form method="POST" action="{{ route('admin.group-tasks.accept', $groupTask) }}">
                        @csrf
                        <div class="gt-form-shell">
                        <div class="form-group">
                            <label>Estimate Type <span class="req">*</span></label>
                            <select class="form-control" name="estimate_type" id="estimate_type" required>
                                <option value="hours">⏱ Hours from now</option>
                                <option value="date">📅 Specific date</option>
                            </select>
                        </div>
                        <div class="form-group" id="hoursBox">
                            <label>How many hours will it take? <span class="req">*</span></label>
                            <input class="form-control" type="number" name="estimated_hours" min="1" value="1">
                        </div>
                        <div class="form-group d-none" id="dateBox">
                            <label>Completion date <span class="req">*</span></label>
                            <input class="form-control" type="date" name="estimated_date"
                                   min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>Narration / Plan</label>
                            <textarea class="form-control" name="accept_narration" rows="3"
                                      placeholder="How will you approach this task?"></textarea>
                        </div>
                        <button class="btn btn-warning btn-block gt-btn" type="submit">
                            <i class="fas fa-check"></i> Accept Task
                        </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($groupTask->status !== 'completed')
            <div class="gt-panel complete-card" style="border-radius:12px;">
                <div class="gt-panel-header" style="background:transparent;border-color:#a7f3d0;">
                    <span style="color:#065f46;">🏁 Submit Completion</span>
                </div>
                <div class="gt-panel-body">
                    <form method="POST" action="{{ route('admin.group-tasks.complete', $groupTask) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="gt-form-shell">
                        <div class="form-group">
                            <label>What was done? <span class="req">*</span></label>
                            <textarea class="form-control" name="completion_narration" id="completion_narration" rows="4"
                                      placeholder="Describe what you did and the outcome…" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Completion Attachments</label>
                            <input class="form-control" type="file" name="completion_attachments[]" multiple>
                        </div>
                        <div class="form-group">
                            <label>Completion Voice Note</label>
                            <div class="voice-recorder-wrap" id="completionVoiceWrap">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <button type="button" class="rec-btn" id="recordCompletionVoice" title="Click to record">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                    <div style="flex:1">
                                        <input class="form-control" type="file" name="completion_voice_note" id="completion_voice_note" accept="audio/*">
                                        <small id="completionRecordStatus" class="text-muted">Upload audio or record from browser.</small>
                                        <div id="completionTranscriptBox" style="display:none;margin-top:8px;">
                                            <small class="text-muted d-block">Live voice text</small>
                                            <div id="completionTranscriptText" style="font-weight:600;color:#1e293b;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div id="completionVoicePlayback" style="display:none;margin-top:10px;"></div>
                            </div>
                        </div>
                        <button class="btn btn-success btn-block gt-btn" type="submit">
                            <i class="fas fa-flag-checkered"></i> Submit Completion
                        </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@section('scripts')
@parent
<script>
(function () {
    var typeSelect = document.getElementById('estimate_type');
    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            document.getElementById('hoursBox').classList.toggle('d-none', this.value !== 'hours');
            document.getElementById('dateBox').classList.toggle('d-none', this.value !== 'date');
        });
    }

    function tick() {
        document.querySelectorAll('[data-deadline]').forEach(function (node) {
            var diff = new Date(node.dataset.deadline).getTime() - Date.now();
            var abs  = Math.abs(diff);
            var h    = Math.floor(abs / 3600000);
            var m    = Math.floor((abs % 3600000) / 60000);
            var s    = Math.floor((abs % 60000) / 1000);
            var chip = node.closest('.countdown-chip');
            var span = node.tagName === 'SPAN' ? node.querySelector('span:last-child') : node;
            if (diff >= 0) {
                if (span) span.textContent = h + 'h ' + m + 'm ' + s + 's left';
                if (chip) chip.className = chip.className.replace(/\b(ok|warning|delayed)\b/g, '') + ' ' + (h < 1 ? 'warning' : 'ok');
            } else {
                if (span) span.textContent = 'Delayed ' + h + 'h ' + m + 'm ' + s + 's';
                if (chip) chip.className = chip.className.replace(/\b(ok|warning|delayed)\b/g, '') + ' delayed';
            }
        });
    }
    tick();
    setInterval(tick, 1000);

    var recordBtn = document.getElementById('recordCompletionVoice');
    var fileInput = document.getElementById('completion_voice_note');
    var statusEl = document.getElementById('completionRecordStatus');
    var playback = document.getElementById('completionVoicePlayback');
    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    var recognition = SpeechRecognition ? new SpeechRecognition() : null;
    var recorder = null;
    var chunks = [];

    if (recognition) {
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-IN';
    }

    if (recordBtn && fileInput) {
        recordBtn.addEventListener('click', async function () {
            if (recorder && recorder.state === 'recording') {
                recorder.stop();
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusEl.textContent = 'Recording not supported.';
                return;
            }

            try {
                var stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                chunks = [];
                recorder = new MediaRecorder(stream);

                recorder.ondataavailable = function (event) {
                    chunks.push(event.data);
                };

                recorder.onstop = function () {
                    stream.getTracks().forEach(function (track) { track.stop(); });
                    if (recognition) recognition.stop();

                    var blob = new Blob(chunks, { type: 'audio/webm' });
                    var file = new File([blob], 'completion-voice-' + Date.now() + '.webm', { type: 'audio/webm' });
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;

                    statusEl.textContent = 'Recording ready: ' + file.name;
                    recordBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                    recordBtn.classList.remove('recording');
                    playback.innerHTML = '<audio controls style="width:100%;" src="' + URL.createObjectURL(blob) + '"></audio>';
                    playback.style.display = 'block';
                };

                recorder.start();

                if (recognition) {
                    var finalText = '';
                    recognition.onresult = function (event) {
                        var interimText = '';
                        for (var i = event.resultIndex; i < event.results.length; i++) {
                            var transcript = event.results[i][0].transcript;
                            if (event.results[i].isFinal) {
                                finalText += transcript + ' ';
                            } else {
                                interimText += transcript;
                            }
                        }

                        var text = (finalText + interimText).trim();
                        document.getElementById('completionTranscriptBox').style.display = text ? 'block' : 'none';
                        document.getElementById('completionTranscriptText').textContent = text;
                        if (text) document.getElementById('completion_narration').value = text;
                    };
                    recognition.start();
                }

                statusEl.textContent = 'Recording... click again to stop';
                recordBtn.innerHTML = '<i class="fas fa-stop"></i>';
                recordBtn.classList.add('recording');
            } catch (error) {
                statusEl.textContent = 'Microphone permission denied.';
            }
        });
    }
})();
</script>
@endsection
