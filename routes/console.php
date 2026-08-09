<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('lines:send-expiration-reminders')->dailyAt('09:00');
Schedule::command('lines:send-expired-notices')->dailyAt('09:15');
Schedule::command('telegram:daily-summary')->dailyAt('22:00');
