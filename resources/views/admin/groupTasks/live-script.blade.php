<script>
    
(function () {
    'use strict';

    /* ── Audio ── */
    var ringAudio  = document.getElementById('homeRingAudio') || document.getElementById('taskRingAudio');
    var ringAlert  = document.getElementById('homeTaskRing')  || document.getElementById('taskRingAlert');
    var ringText   = document.getElementById('homeRingText')  || document.getElementById('taskRingText');
    var ringLink   = document.getElementById('homeRingLink')  || document.getElementById('taskRingLink');

    var isRinging  = false;
    var lastRingId = null;

    /* ── Countdown ── */
    function updateCountdowns() {
        document.querySelectorAll('[data-deadline]').forEach(function (node) {
            var diff = new Date(node.dataset.deadline).getTime() - Date.now();
            var abs  = Math.abs(diff);
            var h    = Math.floor(abs / 3600000);
            var m    = Math.floor((abs % 3600000) / 60000);
            var s    = Math.floor((abs % 60000) / 1000);

            var chip = node.closest ? node.closest('.countdown-chip') : null;

            if (diff >= 0) {
                node.textContent = h + 'h ' + m + 'm ' + s + 's left';
                if (chip) {
                    chip.className = chip.className.replace(/\b(ok|warning|delayed)\b/g, '');
                    chip.classList.add(h < 1 ? 'warning' : 'ok');
                } else {
                    node.style.color = h < 1 ? '#92400e' : '#1e40af';
                }
            } else {
                node.textContent = 'Delayed ' + h + 'h ' + m + 'm';
                if (chip) {
                    chip.className = chip.className.replace(/\b(ok|warning|delayed)\b/g, '');
                    chip.classList.add('delayed');
                } else {
                    node.style.color = '#991b1b';
                }
            }
        });
    }

    /* ── Priority label ── */
    function priorityBadge(p) {
        var map = { low:'🟢 Low', medium:'🟡 Medium', high:'🟠 High', urgent:'🔴 Urgent' };
        var cls = { low:'gt-priority-low', medium:'gt-priority-medium', high:'gt-priority-high', urgent:'gt-priority-urgent' };
        return '<span class="gt-badge ' + (cls[p]||'') + '">' + (map[p]||p) + '</span>';
    }

    /* ── Poll live endpoint ── */
    function pollLive() {
        fetch('{{ route('admin.group-tasks.live') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {

                /* -- Summary metrics -- */
                Object.keys(data.summary || {}).forEach(function (key) {
                    document.querySelectorAll('[data-summary="' + key + '"]').forEach(function (n) {
                        n.textContent = data.summary[key];
                    });
                });

                /* -- Ring / sound -- */
                if (data.ring && data.ring.length) {
                    var task = data.ring[0];
                    if (lastRingId !== task.id) {
                        lastRingId = task.id;
                        if (ringText) ringText.textContent = task.title + ' (' + (task.group || '') + ')';
                        if (ringLink) ringLink.href = task.url;
                        if (ringAlert) ringAlert.classList.add('visible');
                        if (ringAudio && !isRinging) {
                            ringAudio.play().catch(function(){});
                            isRinging = true;
                        }
                    }
                } else {
                    if (ringAlert) ringAlert.classList.remove('visible');
                    if (ringAudio) { ringAudio.pause(); ringAudio.currentTime = 0; }
                    isRinging  = false;
                    lastRingId = null;
                }

                /* -- Active tasks (countdown) -- */
                var activeWrap = document.getElementById('activeTasksWrap');
                var activeTbody = document.getElementById('activeTasksTbody');
                if (activeTbody && data.active_tasks) {
                    if (data.active_tasks.length) {
                        activeWrap && (activeWrap.style.display = '');
                        activeTbody.innerHTML = data.active_tasks.map(function (t) {
                            var cd = t.deadline_iso
                                ? '<span class="countdown-chip ok" data-deadline="' + t.deadline_iso + '"><span class="cd-dot"></span><span>—</span></span>'
                                : '<span class="gt-badge gt-badge-accepted">No Deadline</span>';
                            return '<tr>' +
                                '<td><strong>' + t.title + '</strong></td>' +
                                '<td>' + cd + '</td>' +
                                '<td>' + (t.is_delayed ? '<span class="gt-badge gt-badge-delayed">⚠ Delayed</span>' : '<span class="gt-badge gt-badge-accepted">On Track</span>') + '</td>' +
                                '<td><a href="{{ url('admin/group-tasks') }}/' + t.id + '" class="btn btn-xs btn-primary">Open</a></td>' +
                                '</tr>';
                        }).join('');
                    } else {
                        activeWrap && (activeWrap.style.display = 'none');
                    }
                }

                /* -- Pending (ring) tasks list -- */
                var pendingWrap  = document.getElementById('pendingTasksWrap');
                var pendingTbody = document.getElementById('pendingTasksTbody');
                if (pendingTbody && data.ring) {
                    if (data.ring.length) {
                        pendingWrap && (pendingWrap.style.display = '');
                        pendingTbody.innerHTML = data.ring.map(function (t) {
                            return '<tr>' +
                                '<td><strong>' + t.title + '</strong></td>' +
                                '<td>' + (t.group || '—') + '</td>' +
                                '<td>' + priorityBadge(t.priority) + '</td>' +
                                '<td>' + (t.created_by || '—') + '</td>' +
                                '<td><a href="' + t.url + '" class="btn btn-xs btn-warning">Accept</a></td>' +
                                '</tr>';
                        }).join('');
                    } else {
                        pendingWrap && (pendingWrap.style.display = 'none');
                    }
                }

                /* -- Admin groups grid -- */
                var groupsWrap = document.getElementById('adminGroupsWrap');
                var groupsGrid = document.getElementById('adminGroupsGrid');
                if (groupsGrid && data.groups && data.groups.length) {
                    groupsWrap && (groupsWrap.style.display = '');
                    groupsGrid.innerHTML = data.groups.map(function (g) {
                        return '<div class="home-task-metric" style="--accent:#3b82f6;text-align:left;">' +
                            '<span style="font-weight:700;color:#0f172a;">' + g.name + '</span>' +
                            '<div style="display:flex;gap:8px;margin-top:6px;">' +
                            '<span style="font-size:.7rem;color:#64748b;">👥 ' + g.members_count + ' members</span>' +
                            '<span style="font-size:.7rem;color:#64748b;">📋 ' + g.tasks_count + ' tasks</span>' +
                            '</div>' +
                            '</div>';
                    }).join('');
                }

                /* -- Notifications badge (if header bell exists) -- */
                var notifCount = (data.notifications || []).length;
                var bellBadge = document.getElementById('gtNotifBadge');
                if (bellBadge) bellBadge.textContent = notifCount > 0 ? notifCount : '';

            })
            .catch(function () {});
    }

    /* ── Countdown timer (every 1s for accuracy) ── */
    updateCountdowns();
    setInterval(updateCountdowns, 1000);

    /* ── Polling every 5s ── */
    pollLive();
    setInterval(pollLive, 5000);

})();
</script>
