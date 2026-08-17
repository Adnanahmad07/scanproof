<?php

use App\Jobs\SendDailyReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send daily facility report at 8:00 AM
Schedule::job(new SendDailyReport)->dailyAt('08:00');
