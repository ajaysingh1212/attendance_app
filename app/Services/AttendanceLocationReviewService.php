<?php
namespace App\Services;

use App\Models\AttendanceDetail;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class AttendanceLocationReviewService
{
    public function __construct(private readonly OfficeAreaService $officeAreas, private readonly AttendanceStatusService $attendanceStatuses) {}

    public function update(AttendanceDetail $attendance, Employee $employee, ?float $latitude, ?float $longitude, array $signals = []): array
    {
        return DB::transaction(function () use ($attendance, $employee, $latitude, $longitude, $signals) {
            $attendance = AttendanceDetail::whereKey($attendance->id)->lockForUpdate()->firstOrFail();
            $this->expire($attendance);
            $active = !$attendance->tracking_completed_at && !$attendance->punch_out_time;
            if ($attendance->tracking_enabled) {
                foreach ($signals['events'] ?? [] as $event) {
                    $at = \Carbon\Carbon::parse($event['occurred_at']);
                    // Client timestamps are evidence only, never used to approve attendance.
                    if ($at->lessThan(\Carbon\Carbon::parse($attendance->punch_in_time)) || $at->greaterThan(now()->addMinute())) continue;
                    $end = $attendance->tracking_completed_at ?? ($attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time) : null);
                    if ($end && $at->greaterThan($end)) continue;
                    $attendance->trackingEvents()->firstOrCreate(['client_event_id' => $event['id']], [
                        'type' => $event['type'], 'status' => $attendance->verification_status,
                        'note' => 'Device reported: '.str_replace('_', ' ', $event['type']),
                        'occurred_at' => $at, 'received_at' => now(),
                    ]);
                }
            }
            if ($latitude === null || $longitude === null || !$active) return $this->state($attendance);
            $match = $this->officeAreas->locate($employee, $latitude, $longitude);
            if (!$match['area']) return $this->state($attendance);
            // Once entered, monitor the originally verified office, not a different branch.
            if ($attendance->entered_office_area_at && $attendance->officeArea) {
                $area = $attendance->officeArea;
                $distance = $this->officeAreas->distance($latitude, $longitude, $area->latitude, $area->longitude);
                $match = ['area' => $area, 'distance' => $distance, 'inside' => $distance <= $area->radius_meters];
            }
            if (!$attendance->entered_office_area_at) {
                if ($match['inside']) {
                    $attendance->office_area_id = $match['area']->id;
                    $attendance->entered_office_area_at = now();
                    $attendance->arrival_delay_seconds = $attendance->review_deadline_at ? max(0, now()->timestamp - $attendance->review_deadline_at->timestamp) : 0;
                    if ($attendance->verification_status !== 'suspicious') {
                        $attendance->status = $this->attendanceStatuses->forPunchIn($employee, $attendance->punch_in_time);
                        $attendance->verified_attendance_status = $attendance->status;
                        $attendance->verification_status = 'approved';
                        $attendance->review_note = 'Office location verified. Holding checks apply when enabled.';
                    }
                }
            } elseif ($attendance->tracking_enabled) {
                if (!$match['inside'] && $attendance->last_inside_area !== false) {
                    $area = $match['area'];
                    $attendance->trackingEvents()->create([
                        'type' => 'area_exit', 'status' => 'suspicious',
                        'latitude' => $latitude, 'longitude' => $longitude,
                        'direction' => $this->direction((float) $area->latitude, (float) $area->longitude, $latitude, $longitude),
                        'note' => 'Employee left the office area before holding was completed. Distance: '.round($match['distance']).' m.',
                        'occurred_at' => now(), 'received_at' => now(),
                    ]);
                    $this->suspicious($attendance, 'Employee left the office area before the holding period completed.');
                }
                if ($match['inside'] && $attendance->last_inside_area === false) {
                    $attendance->trackingEvents()->create([
                        'type' => 'office_return', 'status' => $attendance->verification_status,
                        'note' => 'Employee returned to the office. Suspicious status still requires admin review.',
                        'occurred_at' => now(), 'received_at' => now(),
                    ]);
                }
                $attendance->last_inside_area = $match['inside'];
                if ($match['inside'] && $attendance->verification_status === 'holding'
                    && now()->greaterThanOrEqualTo($attendance->holding_deadline_at)) {
                    $attendance->status = $attendance->verified_attendance_status ?: 'present';
                    $attendance->verification_status = 'approved';
                    $attendance->tracking_completed_at = now();
                    $attendance->review_note = 'Holding period completed with office location confirmed.';
                }
            }
            if ($attendance->verification_status === 'in_review' || $attendance->tracking_enabled) {
                $attendance->latest_latitude = $latitude;
                $attendance->latest_longitude = $longitude;
                $attendance->latest_distance_meters = $match['distance'];
                $attendance->last_tracking_at = now();
            }
            if ($attendance->isDirty(['status', 'verification_status', 'entered_office_area_at'])) {
                $attendance->save();
            } else {
                // Heartbeats update current state only; do not create an audit trail of inside positions.
                $attendance->saveQuietly();
            }
            return $this->state($attendance, $match['inside']);
        });
    }

    public function expire(AttendanceDetail $attendance): void
    {
        if ($attendance->verification_status === 'in_review' && $attendance->review_deadline_at?->lessThanOrEqualTo(now())) {
            $this->suspicious($attendance, 'Employee did not reach the office before the arrival deadline.');
            $attendance->save();
        }
        // At the deadline, a recent inside heartbeat completes holding. Subsequent exits cannot reopen it.
        if ($attendance->verification_status === 'holding' && $attendance->holding_deadline_at?->lessThanOrEqualTo(now())
            && $attendance->last_inside_area === true && $attendance->last_tracking_at
            && $attendance->last_tracking_at->greaterThanOrEqualTo($attendance->holding_deadline_at->copy()->subMinutes(6))) {
            $attendance->status = $attendance->verified_attendance_status ?: 'present';
            $attendance->verification_status = 'approved';
            $attendance->tracking_completed_at = $attendance->holding_deadline_at;
            $attendance->review_note = 'Holding period completed with recent office location confirmation.';
            $attendance->save();
        }
        if ($attendance->verification_status === 'holding' && $attendance->last_tracking_at?->copy()->addMinutes(6)->isPast()) {
            $attendance->trackingEvents()->create([
                'type' => 'signal_missing', 'status' => 'suspicious',
                'note' => 'No location heartbeat for over six minutes. Device/network/location reason is unknown.',
                'occurred_at' => $attendance->last_tracking_at->copy()->addMinutes(6), 'received_at' => now(),
            ]);
            $this->suspicious($attendance, 'Tracking signal missing during holding. Admin review required.');
            $attendance->save();
        }
    }

    private function suspicious(AttendanceDetail $attendance, string $note): void
    {
        $attendance->status = 'suspicious';
        $attendance->verification_status = 'suspicious';
        $attendance->review_note = $note;
    }

    public function direction(float $lat1, float $lng1, float $lat2, float $lng2): string
    {
        $delta = deg2rad($lng2 - $lng1);
        $bearing = rad2deg(atan2(sin($delta) * cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos($delta)));
        return ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][((int) round(($bearing + 360) / 45)) % 8];
    }

    private function state(AttendanceDetail $attendance, ?bool $inside = null): array
    {
        $deadline = $attendance->verification_status === 'holding' ? $attendance->holding_deadline_at : $attendance->review_deadline_at;
        return [
            'status' => $attendance->verification_status ?? 'approved', 'attendance_status' => $attendance->status,
            'inside_office_area' => $inside, 'distance_meters' => $attendance->latest_distance_meters,
            'remaining_seconds' => $deadline ? max(0, $deadline->timestamp - now()->timestamp) : 0,
            'overdue_seconds' => $deadline ? max(0, now()->timestamp - $deadline->timestamp) : 0,
            'arrived_at' => $attendance->entered_office_area_at?->toIso8601String(),
            'arrival_delay_seconds' => $attendance->arrival_delay_seconds,
            'holding_deadline_at' => $attendance->holding_deadline_at?->toIso8601String(),
            'tracking_active' => !$attendance->tracking_completed_at && !$attendance->punch_out_time && ($attendance->tracking_enabled || $attendance->verification_status === 'in_review'),
            'check_interval_seconds' => 300,
        ];
    }
}
