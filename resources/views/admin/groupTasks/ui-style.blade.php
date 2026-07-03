<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

/* ══ CSS VARIABLES ═══════════════════════════════════════════ */
:root {
    --gt-bg:        #f1f5fb;
    --gt-surface:   #ffffff;
    --gt-border:    #e4eaf4;
    --gt-shadow:    0 2px 16px rgba(30,58,138,.07);
    --gt-shadow-lg: 0 8px 40px rgba(30,58,138,.12);
    --gt-primary:   #1e3a8a;
    --gt-accent:    #3b82f6;
    --gt-accent2:   #06b6d4;
    --gt-success:   #10b981;
    --gt-warning:   #f59e0b;
    --gt-danger:    #ef4444;
    --gt-text:      #0f172a;
    --gt-muted:     #64748b;
    --gt-radius:    12px;
    --gt-radius-sm: 8px;
    --gt-font:      'Outfit', sans-serif;
    --gt-mono:      'JetBrains Mono', monospace;
}

/* ══ BASE ════════════════════════════════════════════════════ */
.gt-page {
    font-family: var(--gt-font);
    background: var(--gt-bg);
    min-height: 100vh;
    padding: 24px;
}

/* ══ HERO BAR ════════════════════════════════════════════════ */
.gt-hero {
    background: linear-gradient(135deg, var(--gt-primary) 0%, #1e40af 50%, #1d4ed8 100%);
    border-radius: var(--gt-radius);
    padding: 22px 26px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 8px 32px rgba(30,58,138,.25);
    position: relative;
    overflow: hidden;
}
.gt-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.gt-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 100px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.gt-hero-text { position: relative; z-index: 1; }
.gt-title {
    margin: 0;
    color: #fff;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.3px;
}
.gt-subtitle {
    color: rgba(255,255,255,.75);
    margin-top: 4px;
    font-size: .85rem;
    font-weight: 400;
}
.gt-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    position: relative;
    z-index: 1;
}
.gt-btn {
    border-radius: var(--gt-radius-sm) !important;
    font-weight: 600 !important;
    font-family: var(--gt-font) !important;
    font-size: .85rem !important;
    padding: 9px 18px !important;
    transition: all .2s !important;
}
.gt-btn:hover { transform: translateY(-1px); }

/* ══ PANEL ═══════════════════════════════════════════════════ */
.gt-panel {
    background: var(--gt-surface);
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius);
    box-shadow: var(--gt-shadow);
    overflow: hidden;
    margin-bottom: 20px;
}
.gt-panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--gt-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    color: var(--gt-text);
    font-size: .95rem;
    background: #fafcff;
}
.gt-panel-header i { color: var(--gt-accent); margin-right: 7px; }
.gt-panel-body { padding: 20px; }

/* ══ SUMMARY CARDS ═══════════════════════════════════════════ */
.gt-summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(130px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.gt-summary-card {
    background: var(--gt-surface);
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius);
    padding: 16px 18px 14px;
    box-shadow: var(--gt-shadow);
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    cursor: default;
}
.gt-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--gt-shadow-lg);
}
.gt-summary-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 4px;
    background: var(--accent, var(--gt-accent));
    border-radius: 4px 0 0 4px;
}
.gt-summary-card small {
    color: var(--gt-muted);
    font-weight: 600;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.gt-summary-card strong {
    display: block;
    color: var(--gt-text);
    font-size: 2rem;
    line-height: 1;
    margin-top: 8px;
    font-weight: 800;
    font-family: var(--gt-mono);
}
.gt-summary-card .card-icon {
    position: absolute;
    top: 12px; right: 14px;
    font-size: 1.6rem;
    opacity: .15;
}

/* ══ TABLE ═══════════════════════════════════════════════════ */
.gt-table { width: 100%; margin-bottom: 0; }
.gt-table thead th {
    background: #f7faff;
    border-bottom: 2px solid var(--gt-border);
    color: var(--gt-muted);
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    padding: 12px 14px;
    font-weight: 700;
    white-space: nowrap;
}
.gt-table tbody td {
    vertical-align: middle;
    padding: 13px 14px;
    color: var(--gt-text);
    border-bottom: 1px solid #f1f5fb;
    font-size: .88rem;
}
.gt-table tbody tr:last-child td { border-bottom: none; }
.gt-table tbody tr:hover td { background: #f8faff; }
.gt-task-row-title { font-weight: 700; color: var(--gt-text); font-size: .9rem; }
.gt-task-row-meta { color: var(--gt-muted); font-size: .75rem; margin-top: 2px; }

/* Priority dots */
.priority-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-right: 6px;
    vertical-align: middle;
}
.priority-low    .priority-dot { background: #22c55e; }
.priority-medium .priority-dot { background: #eab308; }
.priority-high   .priority-dot { background: #f97316; }
.priority-urgent .priority-dot { background: #ef4444; }

/* ══ BADGES ══════════════════════════════════════════════════ */
.gt-badge {
    border-radius: 999px;
    padding: 4px 11px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.gt-badge-pending   { background: #fef3c7; color: #92400e; }
.gt-badge-accepted  { background: #dbeafe; color: #1e40af; }
.gt-badge-completed { background: #d1fae5; color: #065f46; }
.gt-badge-delayed   { background: #fee2e2; color: #991b1b; }

/* Priority badges */
.gt-priority-low    { background: #dcfce7; color: #166534; }
.gt-priority-medium { background: #fef9c3; color: #713f12; }
.gt-priority-high   { background: #ffedd5; color: #9a3412; }
.gt-priority-urgent { background: #fee2e2; color: #991b1b; }

/* ══ FORM SHELL ══════════════════════════════════════════════ */
.gt-form-shell .form-group { margin-bottom: 18px; }
.gt-form-shell label {
    color: var(--gt-text);
    font-weight: 600;
    font-size: .82rem;
    margin-bottom: 6px;
    display: block;
}
.gt-form-shell label .req { color: var(--gt-danger); margin-left: 2px; }
.gt-form-shell .form-control,
.gt-form-shell .select2-container--default .select2-selection--single,
.gt-form-shell .select2-container--default .select2-selection--multiple {
    border: 1.5px solid var(--gt-border) !important;
    border-radius: var(--gt-radius-sm) !important;
    min-height: 42px !important;
    font-family: var(--gt-font) !important;
    font-size: .88rem !important;
    color: var(--gt-text) !important;
    transition: border-color .2s !important;
    background: #fafcff !important;
}
.gt-form-shell .form-control:focus,
.gt-form-shell .select2-container--default .select2-selection--single:focus {
    border-color: var(--gt-accent) !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12) !important;
    background: #fff !important;
}
.gt-form-shell textarea.form-control { min-height: 100px; resize: vertical; }

/* ══ COUNTDOWN ═══════════════════════════════════════════════ */
.countdown-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--gt-mono);
    font-size: .82rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    white-space: nowrap;
}
.countdown-chip.ok      { background: #dbeafe; color: #1e40af; }
.countdown-chip.warning { background: #fef3c7; color: #92400e; }
.countdown-chip.delayed { background: #fee2e2; color: #991b1b; }
.countdown-chip .cd-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse 1s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

/* ══ MEMBER CHIPS ════════════════════════════════════════════ */
.member-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5fb;
    border: 1px solid var(--gt-border);
    border-radius: 999px;
    padding: 4px 10px 4px 4px;
    font-size: .78rem;
    font-weight: 500;
    color: var(--gt-text);
    margin: 2px;
}
.member-chip .mc-avatar {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gt-primary), var(--gt-accent));
    color: #fff;
    font-size: .62rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ══ TASK DETAIL CARD ════════════════════════════════════════ */
.td-section {
    background: #fafcff;
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius);
    padding: 20px;
    margin-bottom: 16px;
}
.td-section-title {
    font-weight: 800;
    color: var(--gt-primary);
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.td-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--gt-border);
}

/* Timeline */
.gt-timeline { border-left: 2px solid var(--gt-border); padding-left: 18px; margin: 0; }
.gt-timeline-item {
    position: relative;
    padding-bottom: 18px;
}
.gt-timeline-item:last-child { padding-bottom: 0; }
.gt-timeline-item::before {
    content: '';
    position: absolute;
    left: -23px; top: 5px;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--gt-border);
    border: 2px solid var(--gt-surface);
}
.gt-timeline-item.done::before  { background: var(--gt-success); }
.gt-timeline-item.active::before { background: var(--gt-accent); box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
.gt-timeline-label { font-weight: 700; font-size: .82rem; color: var(--gt-text); }
.gt-timeline-meta  { font-size: .75rem; color: var(--gt-muted); margin-top: 2px; }
.gt-timeline-note  { font-size: .83rem; color: var(--gt-text); margin-top: 6px; background: #fff; border: 1px solid var(--gt-border); border-radius: var(--gt-radius-sm); padding: 8px 12px; }

/* ══ HOME DASHBOARD WIDGET ═══════════════════════════════════ */
.home-task-dashboard {
    background: var(--gt-surface);
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius);
    padding: 20px;
    margin-bottom: 22px;
    box-shadow: var(--gt-shadow);
}
.home-task-ring {
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #fff1f2;
    border: 1.5px solid #fecdd3;
    color: #9f1239;
    border-radius: var(--gt-radius-sm);
    padding: 12px 16px;
    margin-bottom: 14px;
    animation: ringPulse 1s ease-in-out infinite;
}
@keyframes ringPulse { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.15)} 50%{box-shadow:0 0 0 6px rgba(239,68,68,.0)} }
.home-task-ring.visible { display: flex; }
.home-task-open {
    background: #9f1239;
    color: #fff;
    border-radius: 6px;
    padding: 6px 12px;
    font-weight: 700;
    font-size: .8rem;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}
.home-task-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.home-task-head h4 { margin: 0; color: var(--gt-text); font-size: 1rem; font-weight: 800; }
.home-task-head p  { margin: 2px 0 0; color: var(--gt-muted); font-size: .78rem; }
.home-task-actions { display: flex; gap: 7px; flex-wrap: wrap; }
.home-task-actions a {
    border: 1.5px solid var(--gt-border);
    color: var(--gt-text);
    background: #fafcff;
    border-radius: 7px;
    padding: 7px 13px;
    font-weight: 600;
    font-size: .8rem;
    text-decoration: none;
    transition: all .2s;
    font-family: var(--gt-font);
}
.home-task-actions a:hover { border-color: var(--gt-accent); color: var(--gt-accent); }
.home-task-actions a.primary {
    background: var(--gt-accent);
    color: #fff;
    border-color: var(--gt-accent);
}
.home-task-actions a.primary:hover { background: #2563eb; }
.home-task-metrics {
    display: grid;
    grid-template-columns: repeat(5, minmax(110px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.home-task-metric {
    background: #f8faff;
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius-sm);
    padding: 12px 14px;
    border-left: 3px solid var(--accent, var(--gt-accent));
    transition: transform .15s;
}
.home-task-metric:hover { transform: translateY(-1px); }
.home-task-metric span {
    color: var(--gt-muted);
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    display: block;
}
.home-task-metric strong {
    display: block;
    font-size: 1.5rem;
    color: var(--gt-text);
    line-height: 1;
    margin-top: 6px;
    font-family: var(--gt-mono);
    font-weight: 700;
}
.home-task-table-wrap {
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius-sm);
    overflow: hidden;
}
.home-task-table-title {
    padding: 10px 14px;
    background: #f7faff;
    color: var(--gt-text);
    font-weight: 700;
    font-size: .82rem;
    border-bottom: 1px solid var(--gt-border);
}
.home-task-table { margin-bottom: 0; }
.home-task-table thead th {
    background: #fafcff;
    color: var(--gt-muted);
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 700;
    padding: 10px 12px;
}
.home-task-table tbody td { padding: 10px 12px; font-size: .83rem; }

/* Notification dot */
.notif-dot {
    width: 8px; height: 8px;
    background: var(--gt-danger);
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    flex-shrink: 0;
    animation: pulse 1.5s infinite;
}

/* ══ VOICE RECORDER ══════════════════════════════════════════ */
.voice-recorder-wrap {
    background: #fafcff;
    border: 1.5px dashed var(--gt-border);
    border-radius: var(--gt-radius-sm);
    padding: 14px;
}
.voice-recorder-wrap.recording {
    border-color: var(--gt-danger);
    background: #fff5f5;
}
.rec-btn {
    width: 44px; height: 44px;
    border-radius: 50%;
    border: none;
    background: var(--gt-danger);
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rec-btn:hover { transform: scale(1.1); }
.rec-btn.recording { animation: recPulse .8s ease-in-out infinite; }
@keyframes recPulse { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4)} 70%{box-shadow:0 0 0 8px transparent} }

/* ══ GROUP CARDS (taskGroups/index) ══════════════════════════ */
.group-card {
    background: var(--gt-surface);
    border: 1px solid var(--gt-border);
    border-radius: var(--gt-radius);
    padding: 20px;
    box-shadow: var(--gt-shadow);
    transition: all .25s;
    height: 100%;
    position: relative;
    overflow: hidden;
}
.group-card:hover {
    border-color: var(--gt-accent);
    box-shadow: var(--gt-shadow-lg);
    transform: translateY(-2px);
}
.group-card-accent {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gt-primary), var(--gt-accent2));
}
.group-card-title { font-weight: 800; font-size: 1rem; color: var(--gt-text); margin-bottom: 4px; }
.group-card-desc  { font-size: .8rem; color: var(--gt-muted); margin-bottom: 14px; }
.group-stat-row   { display: flex; gap: 10px; margin-bottom: 14px; }
.group-stat {
    background: #f1f5fb;
    border-radius: 8px;
    padding: 8px 12px;
    flex: 1;
    text-align: center;
}
.group-stat .gs-num  { font-size: 1.2rem; font-weight: 800; color: var(--gt-text); font-family: var(--gt-mono); display: block; }
.group-stat .gs-lbl  { font-size: .66rem; color: var(--gt-muted); font-weight: 600; text-transform: uppercase; }
.group-card-members  { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 14px; }
.group-card-actions  { display: flex; gap: 8px; }

/* ══ RESPONSIVE ══════════════════════════════════════════════ */
@media (max-width: 991px) {
    .gt-hero { flex-direction: column; align-items: flex-start; }
    .gt-actions { justify-content: flex-start; }
    .gt-summary-grid { grid-template-columns: repeat(3, 1fr); }
    .home-task-metrics { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 640px) {
    .gt-page { padding: 14px; }
    .gt-summary-grid { grid-template-columns: repeat(2, 1fr); }
    .home-task-metrics { grid-template-columns: 1fr 1fr; }
}
</style>
