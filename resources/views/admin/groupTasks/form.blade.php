@php
    $selectedGroup = old(
        'task_group_id',
        request('group_id', optional($groupTask)->task_group_id)
    );

    $selectedAssignees = old(
        'assignees',
        $groupTask
            ? $groupTask->assignees->pluck('id')->toArray()
            : []
    );

    $effectiveGroup = $selectedGroup ?: optional($groups->first())->id;

    $membersByGroup = $groups->mapWithKeys(function ($group) {

        return [
            $group->id => $group->members->map(function ($m) {

                return [
                    'id'   => $m->id,
                    'name' => $m->name,
                ];

            })->values()->toArray()
        ];
    });

    $initialMembers = $effectiveGroup
        ? ($membersByGroup[$effectiveGroup] ?? [])
        : [];
@endphp

<div class="gt-form-shell">
<div class="row">

    {{-- Group --}}
    <div class="form-group col-lg-4">
        <label>Group <span class="req">*</span></label>

        <select class="form-control select2"
                name="task_group_id"
                id="task_group_id"
                required>

            <option value="">— Select Group —</option>

            @foreach($groups as $group)

                <option value="{{ $group->id }}"
                    {{
                        (int)$effectiveGroup === $group->id
                            ? 'selected'
                            : ''
                    }}>
                    {{ $group->name }}
                </option>

            @endforeach

        </select>
    </div>

    {{-- Title --}}
    <div class="form-group col-lg-5">
        <label>Task Title <span class="req">*</span></label>

        <input class="form-control"
               type="text"
               name="title"
               id="title"
               value="{{ old('title', optional($groupTask)->title) }}"
               placeholder="Describe the task clearly…"
               required>
    </div>

    {{-- Priority --}}
    <div class="form-group col-lg-3">
        <label>Priority <span class="req">*</span></label>

        <select class="form-control"
                name="priority"
                id="priority"
                required>

            @foreach([
                'low'=>'🟢 Low',
                'medium'=>'🟡 Medium',
                'high'=>'🟠 High',
                'urgent'=>'🔴 Urgent'
            ] as $k => $l)

                <option value="{{ $k }}"
                    {{
                        old(
                            'priority',
                            optional($groupTask)->priority ?? 'medium'
                        ) === $k
                            ? 'selected'
                            : ''
                    }}>
                    {{ $l }}
                </option>

            @endforeach

        </select>
    </div>

    {{-- Description --}}
    <div class="form-group col-lg-12">
        <label>Task Details</label>

        <textarea class="form-control"
                  name="description"
                  id="description"
                  rows="4"
                  placeholder="Add detailed description, requirements, notes…">{{ old('description', optional($groupTask)->description) }}</textarea>
        <small class="text-muted" id="taskSpeechStatus"></small>
    </div>

    {{-- Due Date --}}
    <div class="form-group col-lg-4">
        <label>Due Date / Time</label>

        <input class="form-control"
               type="datetime-local"
               name="due_at"
               id="due_at"
               value="{{ old('due_at', optional(optional($groupTask)->due_at)->format('Y-m-d\TH:i')) }}">
    </div>

    {{-- Assignees --}}
    <div class="form-group col-lg-8">
        <label>Assign Members <span class="req">*</span></label>

        <select class="form-control select2"
                name="assignees[]"
                id="assignees"
                multiple
                required>

            @foreach($initialMembers as $member)

                <option value="{{ $member['id'] }}"
                    {{
                        in_array(
                            $member['id'],
                            array_map('intval', $selectedAssignees)
                        )
                            ? 'selected'
                            : ''
                    }}>
                    {{ $member['name'] }}
                </option>

            @endforeach

        </select>

        <small class="text-muted" id="assigneeHelp">
            {{
                count($initialMembers)
                    ? count($initialMembers).' member(s) available.'
                    : 'Select a group first.'
            }}
        </small>
    </div>

    {{-- Attachments --}}
    <div class="form-group col-lg-6">
        <label>
            Attachments
            <small class="text-muted">
                (multiple files allowed)
            </small>
        </label>

        <input class="form-control"
               type="file"
               name="attachments[]"
               id="attachments"
               multiple
               accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip,.txt">

        <small class="text-muted">
            Max 20 MB per file.
        </small>
    </div>

    {{-- Voice Note --}}
    <div class="form-group col-lg-6">

        <label>
            Voice Note
            <small class="text-muted">
                (upload or record)
            </small>
        </label>

        <div class="voice-recorder-wrap" id="voiceWrap">

            <div style="display:flex;align-items:center;gap:10px;">

                <button type="button"
                        class="rec-btn"
                        id="recordVoice"
                        title="Click to record">

                    <i class="fas fa-microphone"></i>
                </button>

                <div style="flex:1">

                    <input class="form-control"
                           type="file"
                           name="voice_note"
                           id="voice_note"
                           accept="audio/*"
                           style="margin-bottom:6px;">

                    <small id="recordStatus"
                           class="text-muted">

                        Upload audio or record from browser.
                    </small>
                    <div class="speech-box mt-2" id="voiceTranscriptBox" style="display:none;">
                        <small class="text-muted d-block">Live voice text</small>
                        <div id="voiceTranscriptText" style="font-weight:600;color:#1e293b;"></div>
                    </div>
                </div>
            </div>

            <div id="voicePlayback"
                 style="display:none;margin-top:10px;">
            </div>
        </div>
    </div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ─────────────────────────────
     | DATA
     ───────────────────────────── */

    var membersByGroup =
        @json($membersByGroup);

    var selectedAssignees =
        @json(array_map('intval', $selectedAssignees));

    var groupSelect =
        document.getElementById('task_group_id');

    var assigneeSelect =
        document.getElementById('assignees');

    var help =
        document.getElementById('assigneeHelp');

    /* ─────────────────────────────
     | SELECT2 INIT
     ───────────────────────────── */

    if (window.jQuery) {

        $('#task_group_id').select2({
            width: '100%'
        });

        $('#assignees').select2({
            width: '100%',
            placeholder: 'Select members'
        });
    }

    /* ─────────────────────────────
     | MEMBERS LOAD
     ───────────────────────────── */

    function rebuildAssignees(groupId) {

        var members = membersByGroup[groupId] || [];

        // destroy select2
        if (
            window.jQuery &&
            $('#assignees').hasClass('select2-hidden-accessible')
        ) {
            $('#assignees').select2('destroy');
        }

        // clear
        assigneeSelect.innerHTML = '';

        // append
        members.forEach(function(member) {

            var option =
                document.createElement('option');

            option.value = member.id;
            option.text  = member.name;

            if (
                selectedAssignees.includes(
                    parseInt(member.id)
                )
            ) {
                option.selected = true;
            }

            assigneeSelect.appendChild(option);
        });

        // re-init select2
        if (window.jQuery) {

            $('#assignees').select2({
                width: '100%',
                placeholder: 'Select members'
            });
        }

        // help text
        help.innerHTML = members.length
            ? members.length +
              ' member(s) available in selected group.'
            : '<span style="color:red;">No members found.</span>';
    }

    /* ─────────────────────────────
     | GROUP CHANGE
     ───────────────────────────── */

    if (groupSelect) {

        groupSelect.addEventListener('change', function () {

            selectedAssignees = [];

            rebuildAssignees(this.value);
        });

        // initial load
        rebuildAssignees(groupSelect.value);
    }

    /* ─────────────────────────────
     | VOICE RECORDER
     ───────────────────────────── */

    var recBtn =
        document.getElementById('recordVoice');

    var fileInput =
        document.getElementById('voice_note');

    var statusEl =
        document.getElementById('recordStatus');

    var playback =
        document.getElementById('voicePlayback');

    var voiceWrap =
        document.getElementById('voiceWrap');

    var recorder = null;
    var chunks   = [];
    var SpeechRecognition =
        window.SpeechRecognition ||
        window.webkitSpeechRecognition;
    var recognition = null;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-IN';
    }

    if (recBtn) {

        recBtn.addEventListener('click', async function () {

            if (
                recorder &&
                recorder.state === 'recording'
            ) {
                recorder.stop();
                return;
            }

            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {

                statusEl.textContent =
                    'Recording not supported.';

                return;
            }

            try {

                var stream =
                    await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });

                chunks = [];

                recorder =
                    new MediaRecorder(stream);

                recorder.ondataavailable =
                    function (e) {

                    chunks.push(e.data);
                };

                recorder.onstop = function () {

                    stream.getTracks().forEach(
                        function (t) {
                            t.stop();
                        }
                    );

                    if (recognition) {
                        recognition.stop();
                    }

                    var blob =
                        new Blob(chunks, {
                            type: 'audio/webm'
                        });

                    var file =
                        new File(
                            [blob],
                            'voice-' + Date.now() + '.webm',
                            {
                                type: 'audio/webm'
                            }
                        );

                    var dt =
                        new DataTransfer();

                    dt.items.add(file);

                    fileInput.files = dt.files;

                    statusEl.textContent =
                        '✅ Recording ready: ' +
                        file.name;

                    recBtn.innerHTML =
                        '<i class="fas fa-microphone"></i>';

                    recBtn.classList.remove(
                        'recording'
                    );

                    voiceWrap.classList.remove(
                        'recording'
                    );

                    var url =
                        URL.createObjectURL(blob);

                    playback.innerHTML =
                        '<audio controls style="width:100%;" src="' +
                        url +
                        '"></audio>';

                    playback.style.display =
                        'block';
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
                        document.getElementById('voiceTranscriptBox').style.display = text ? 'block' : 'none';
                        document.getElementById('voiceTranscriptText').textContent = text;

                        var description = document.getElementById('description');
                        if (description && text) {
                            description.value = text;
                        }
                    };
                    recognition.start();
                }

                statusEl.textContent =
                    '🔴 Recording... click again to stop';

                recBtn.innerHTML =
                    '<i class="fas fa-stop"></i>';

                recBtn.classList.add(
                    'recording'
                );

                voiceWrap.classList.add(
                    'recording'
                );

            } catch (err) {

                statusEl.textContent =
                    'Microphone permission denied.';
            }
        });
    }

});
</script>
