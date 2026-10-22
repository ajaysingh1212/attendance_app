# Attendance holding and location checks

Employee create/edit has an admin-only tracking toggle and holding hours (default 1, range 0.08?24). Existing employees default to tracking disabled. Configure an office area and assign the employee to the correct branch. Anywhere employees have no office geofence.

The employee policy is copied to each attendance at punch-in; later employee edits affect subsequent attendance only. An outside punch starts the existing office arrival timer. Late arrival remains suspicious. Timely office verification starts holding when enabled: attendance status is `in_review`, verification status is `holding`. Every five minutes a fresh location check updates current heartbeat state. Inside coordinates are not added to the journey or heartbeat audit history. Each transition outside records its observed time, coordinates, office-relative compass direction and suspicious status. Returning does not clear suspicious status. The exact movement path or departure instant between checks cannot be inferred from sampled positions.

At holding expiry, a recent inside heartbeat (within six minutes of the deadline) completes attendance and restores the normal present/half-day policy. Later exits have no effect. A gap over six minutes during holding requires admin review; absence of data is never labelled as a proven internet or GPS switch-off. Punching out before holding completes is suspicious. Only an admin can clear suspicious status. Admin approval/rejection ends tracking for that attendance.

## Server setup

Apply the new migration and its existing office-area prerequisites:

```sh
php artisan migrate --path=database/migrations/2026_09_23_000003_add_branch_id_to_office_areas.php
php artisan migrate --path=database/migrations/2026_09_23_000005_add_arrival_delay_to_attendance_details.php
php artisan migrate --path=database/migrations/2026_10_22_000001_add_attendance_holding_tracking.php
```

Run Laravel's scheduler every minute (`php artisan schedule:run` through cron/Task Scheduler), or use `php artisan schedule:work` in development. The named `attendance-tracking-expiry` task expires arrival reviews, flags missing signals and completes holding with recent inside confirmation. It must run when no dashboard is open. The dashboard feed also reconciles pending records when read.

## Browser behavior and limits

All authenticated employee pages use the shared tracker. Fresh position requests run every 300 seconds while tracking remains active; reconnect and page visibility changes trigger an additional check. Device-reported network/location availability events are queued in localStorage with unique IDs and original device timestamps, then acknowledged by the server on reconnect. Server receipt time is stored separately. Location errors may mean permission denied, GPS unavailable or a timeout. Browsers can suspend background tabs or stop execution when closed; uninterrupted tracking with the app closed requires native mobile background-location support. A server cannot record an offline device event until it reconnects. A missing heartbeat is the fallback evidence.

## Mobile integration (client source is not in this repository)

The Laravel API is ready, but mobile clients must implement their own five-minute background heartbeat, offline queue and new holding UI. Existing punch/verify requests alone cannot provide ongoing tracking.

Send `POST /api/v1/attendance/location` with a Sanctum bearer token. The employee is always resolved from the authenticated user; `attendance_id` is optional and, if supplied, must belong to that user.

```json
{
  "attendance_id": 123,
  "latitude": 28.6001,
  "longitude": 77.2001,
  "events": [
    {"id": "a-device-generated-uuid", "type": "network_offline", "occurred_at": "2026-10-22T09:02:00+05:30"}
  ]
}
```

Coordinates may be omitted when reporting unavailable location. Both must be supplied together otherwise. Event types: `network_offline`, `network_online`, `location_unavailable`, `location_available`. Maximum 100 events per request. Retry with the same IDs; ingestion is idempotent. Events outside the attendance period or in the future are ignored. Device timestamps never approve attendance.

The response includes `status`, `attendance_status`, `remaining_seconds`, `holding_deadline_at`, `tracking_active`, and `check_interval_seconds: 300`. Keep collecting during `holding` and suspicious review until `tracking_active` becomes false. Show holding separately from completed attendance. Office verification uses server geofence calculations, not the client-supplied IN_LOCATION flag.

## Leave requests

A new leave must start tomorrow or later in the application's configured timezone; this is a calendar-date rule, not a rolling 24-hour interval. End date cannot precede start date. Unchanged historical request dates remain editable for approval. Changing the start date revalidates the future-date rule. Validation also applies to API and model writes. The application timestamp is server generated, and list/edit/detail pages display application time and requested dates.

## Verification

Run `php vendor/bin/phpunit tests/Feature/AttendanceHoldingTest.php tests/Unit --no-progress`. Feature tests use an isolated in-memory SQLite database and do not modify attendance records in the configured application database.
