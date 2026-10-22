<?php

namespace App\Services;

use App\Models\AttendanceDetail;

class AttendanceCorrectionService
{
    public function summarize(AttendanceDetail $attendance): array
    {
        $event = $attendance->trackingEvents->last(fn ($event) => in_array($event->type, ['admin_edit', 'admin_review', 'admin_recorded'], true));
        $editor = $attendance->changedBy;
        // A self-punch also sets changed_by; it is not evidence of an admin correction.
        $differentEditor = $editor && (string) $editor->id !== (string) $attendance->user_id;
        $privilegedEditor = $differentEditor && $editor->roles->contains(fn ($role) => (int) $role->id === 1 || in_array(strtolower($role->title), ['admin', 'hr'], true));
        $legacyName = !is_numeric($attendance->changed_by) ? trim((string) $attendance->changed_by) : '';
        $legacyName = in_array(strtolower($legacyName), ['', 'system', 'auto', 'employee'], true) ? '' : $legacyName;
        $corrected = $event !== null || $privilegedEditor || $legacyName !== '';

        return [
            'is_corrected' => (bool) $corrected,
            'reviewed_by_name' => $corrected ? ($privilegedEditor ? $editor->name : ($legacyName ?: null)) : null,
            'reviewed_by_role' => $corrected ? ($privilegedEditor && !$editor->roles->contains('id', 1) ? 'HR' : 'Admin') : null,
            // updated_at may be a later heartbeat; do not invent an edit time from it.
            'reviewed_at' => $event?->occurred_at?->toIso8601String(),
            'review_note' => $corrected ? ($event?->note ?: $attendance->review_note) : null,
        ];
    }
}
