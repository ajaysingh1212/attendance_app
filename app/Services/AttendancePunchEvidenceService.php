<?php

namespace App\Services;

use App\Models\AttendanceDetail;

class AttendancePunchEvidenceService
{
    public function __construct(private readonly OfficeAreaService $officeAreas) {}

    public function summarize(AttendanceDetail $attendance): array
    {
        $employee = $attendance->employee;
        $anywhere = $employee && (strtolower(trim((string) $employee->attendance_source)) === 'anywhere'
            || strtolower(trim((string) $employee->branch_id)) === 'anywhere');
        $area = $attendance->officeArea;
        $distance = is_numeric($attendance->punch_distance_meters) && $attendance->punch_distance_meters >= 0
            ? (float) $attendance->punch_distance_meters : null;
        $source = $distance !== null ? 'recorded' : 'unavailable';
        $radius = $area?->radius_meters;
        $reference = $area?->name;
        $latitude = $attendance->punch_in_latitude;
        $longitude = $attendance->punch_in_longitude;
        $hasCoordinates = is_numeric($latitude) && is_numeric($longitude)
            && abs((float) $latitude) <= 90 && abs((float) $longitude) <= 180;

        // Historical punches are never rewritten using today's employee policy or office coordinates.
        if ($distance === null && $hasCoordinates) {
            if (!$area && $employee && !$anywhere) {
                $match = $this->officeAreas->locate($employee, (float) $latitude, (float) $longitude);
                $area = $match['area'];
            }
            if ($area) {
                $distance = $this->officeAreas->distance((float) $latitude, (float) $longitude, (float) $area->latitude, (float) $area->longitude);
                $reference = $area->name;
                $radius = $area->radius_meters;
            } elseif ($employee) {
                $branch = $employee->branch ?? $employee->officeBranch;
                if ($branch && is_numeric($branch->latitude) && is_numeric($branch->longitude)
                    && abs((float) $branch->latitude) <= 90 && abs((float) $branch->longitude) <= 180) {
                    $distance = $this->officeAreas->distance((float) $latitude, (float) $longitude, (float) $branch->latitude, (float) $branch->longitude);
                    $reference = $branch->title ?? $branch->branch_name ?? 'Assigned office';
                    $radius = $employee->attendance_radius_meter > 0 ? (float) $employee->attendance_radius_meter : null;
                }
            }
            if ($distance !== null) $source = 'recalculated';
        }
        $inside = $distance !== null && $radius !== null ? $distance <= $radius : null;
        $verification = $attendance->verification_status;
        $outcome = ucwords(str_replace('_', ' ', $attendance->status ?? 'Unknown'));
        if ($attendance->review_note) {
            $reason = $attendance->review_note;
        } elseif ($verification === 'holding') {
            $reason = 'Office location verified; the holding period is still running.';
        } elseif ($verification === 'suspicious') {
            $reason = 'Attendance requires admin review. Present is not confirmed.';
        } elseif (in_array($verification, ['approved', 'verified'], true)) {
            $reason = 'Office verification or approval is recorded. Attendance outcome: '.$outcome.'.';
        } elseif ($anywhere) {
            $reason = $outcome.' is recorded without a separate office verification result. The employee is currently configured for Anywhere attendance; office entry is not required under that policy.';
        } else {
            $reason = $outcome.' is the saved attendance outcome. No separate location-verification result was recorded; this status alone does not prove office entry.';
        }
        if ($source === 'recalculated') {
            $reason .= ' Distance is recalculated from saved punch-in GPS against the current office pin; historical geofence validation cannot be confirmed from this calculation.';
        } elseif ($source === 'unavailable') {
            $reason .= $hasCoordinates ? ' Office pin reference is unavailable, so punch-in distance cannot be determined.' : ' Punch-in distance and usable GPS evidence are unavailable.';
        }
        return [
            'distance_meters' => $distance, 'radius_meters' => $radius, 'area_name' => $reference,
            'distance_source' => $source, 'inside_current_radius' => $inside,
            'location_verification' => $verification ? ucwords(str_replace('_', ' ', $verification)) : 'Not recorded',
            'current_location_policy' => $employee ? ($anywhere ? 'Anywhere' : 'Office based') : 'Unknown',
            'reason' => $reason,
        ];
    }
}
