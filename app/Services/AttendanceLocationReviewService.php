<?php

namespace App\Services;

use App\Models\AttendanceDetail;
use App\Models\Employee;

class AttendanceLocationReviewService
{
    public function __construct(
        private readonly OfficeAreaService $officeAreas,
        private readonly AttendanceStatusService $attendanceStatuses,
    ) {
    }

    public function update(AttendanceDetail $attendance, Employee $employee, ?float $latitude, ?float $longitude): array
    {
        $now = now();
        $deadline = $attendance->review_deadline_at;
        $expired = $deadline && $now->greaterThanOrEqualTo($deadline);

        if (!in_array($attendance->verification_status, ['in_review', 'suspicious', 'verified'], true)) {
            return $this->state($attendance);
        }

        if ($expired && $attendance->verification_status === 'in_review') {
            $attendance->update([
                'status' => 'suspicious',
                'verification_status' => 'suspicious',
                'review_note' => 'Employee did not enter the office area before the review timer expired.',
            ]);
        }

        if ($latitude === null || $longitude === null || $attendance->entered_office_area_at) {
            return $this->state($attendance->fresh());
        }

        $match = $this->officeAreas->locate($employee, $latitude, $longitude);
        $updates = [
            'latest_latitude' => $latitude,
            'latest_longitude' => $longitude,
            'latest_distance_meters' => $match['distance'],
        ];

        if ($match['inside']) {
            $arrivalDelay = $deadline ? max(0, $now->timestamp - $deadline->timestamp) : 0;
            $updates['office_area_id'] = $match['area']->id;
            $updates['entered_office_area_at'] = $now;
            $updates['arrival_delay_seconds'] = $arrivalDelay;

            if ($expired || $attendance->verification_status === 'suspicious') {
                $updates['status'] = 'suspicious';
                $updates['verification_status'] = 'suspicious';
                $updates['review_note'] = "Employee reached the office area {$arrivalDelay} seconds after the review deadline.";
            } else {
                $calculatedStatus = $this->attendanceStatuses->forPunchIn($employee, $attendance->punch_in_time);
                $updates += [
                    'status' => $calculatedStatus,
                    'verified_attendance_status' => $calculatedStatus,
                    'verification_status' => 'approved',
                    'review_note' => 'Employee entered the office area before the review timer expired.',
                ];
            }
        }

        $attendance->update($updates);

        return $this->state($attendance->fresh(), $match['inside'], $match['distance']);
    }

    private function state(AttendanceDetail $attendance, ?bool $inside = null, mixed $distance = null): array
    {
        $now = now()->timestamp;
        $deadline = $attendance->review_deadline_at?->timestamp;

        return [
            'status' => $attendance->verification_status ?? 'approved',
            'attendance_status' => $attendance->status,
            'inside_office_area' => $inside,
            'distance_meters' => $distance ?? $attendance->latest_distance_meters,
            'remaining_seconds' => $deadline ? max(0, $deadline - $now) : 0,
            'overdue_seconds' => $deadline ? max(0, $now - $deadline) : 0,
            'arrived_at' => $attendance->entered_office_area_at?->toIso8601String(),
            'arrival_delay_seconds' => $attendance->arrival_delay_seconds,
        ];
    }
}
