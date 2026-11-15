<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Resrv maintenance. Both commands only run when the server's cron calls
 * `php artisan schedule:run` every minute.
 *
 * - cancel-lapsed-holds: cancels CP-created "awaiting payment" reservations once
 *   their payment deadline passes, releasing the availability they hold.
 * - housekeeping: prunes long-expired reservations, orphaned customers and
 *   availability for past dates (default retention: 30 days).
 */
Schedule::command('resrv:cancel-lapsed-holds')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('resrv:housekeeping')->daily();
