<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AttendanceDetail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoMarkAbsentUsers extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Mark all users as absent at the start of the day';

   public function handle()
{
    \Log::info('🔁 Starting attendance:mark-absent command');

    $today = \Carbon\Carbon::today();
    $dayOfWeek = $today->format('l'); // Sunday, Monday, etc.

    // Only Sunday should be week_off
    $defaultStatus = ($dayOfWeek === 'Sunday') ? 'week_off' : 'absent';

    $alreadyMarked = \App\Models\AttendanceDetail::whereDate('created_at', $today)
        ->pluck('user_id')
        ->toArray();

    \Log::info("🧾 Already marked users: " . json_encode($alreadyMarked));
    \Log::info("📅 Today is $dayOfWeek, status to mark: $defaultStatus");

    $users = \App\Models\User::whereNotIn('id', $alreadyMarked)->get();

    if ($users->isEmpty()) {
        \Log::info('✅ No new users to mark.');
        return;
    }

    foreach ($users as $user) {
        try {
            \App\Models\AttendanceDetail::create([
                'user_id'    => $user->id,
                'status'     => $defaultStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            \Log::info("✅ Marked $defaultStatus for user_id: {$user->id}");
        } catch (\Exception $e) {
            \Log::error("❌ Failed for user_id: {$user->id} — " . $e->getMessage());
        }
    }

    \Log::info("✅ attendance:mark-absent command completed.");
}

}
