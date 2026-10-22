<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run Laravel's scheduler every minute in production.
\Illuminate\Support\Facades\Schedule::call(function () {
    \App\Models\AttendanceDetail::whereIn('verification_status', ['in_review', 'holding'])
        ->eachById(function ($item) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($item) {
                $attendance = \App\Models\AttendanceDetail::whereKey($item->id)->lockForUpdate()->first();
                app(\App\Services\AttendanceLocationReviewService::class)->expire($attendance);
            });
        });
})->everyMinute()->name('attendance-tracking-expiry')->withoutOverlapping();
