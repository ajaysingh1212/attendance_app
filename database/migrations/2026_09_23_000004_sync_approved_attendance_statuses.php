<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attendance_details')
            ->join('employees', 'employees.id', '=', 'attendance_details.employee_id')
            ->where('attendance_details.verification_status', 'approved')
            ->whereNotNull('attendance_details.punch_in_time')
            ->select([
                'attendance_details.id',
                'attendance_details.punch_in_time',
                'employees.work_start_time',
                'employees.delay_time',
            ])
            ->orderBy('attendance_details.id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $punch = Carbon::parse($row->punch_in_time);
                    $status = 'present';
                    if ($row->work_start_time) {
                        $allowedUntil = $punch->copy()
                            ->setTimeFromTimeString(Carbon::parse($row->work_start_time)->format('H:i:s'))
                            ->addMinutes((int) ($row->delay_time ?? 0));
                        $status = $punch->greaterThan($allowedUntil) ? 'half_time' : 'present';
                    }

                    DB::table('attendance_details')->where('id', $row->id)->update([
                        'status' => $status,
                        'verified_attendance_status' => $status,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Status synchronization is intentionally not reversible.
    }
};
