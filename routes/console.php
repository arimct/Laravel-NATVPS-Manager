<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// VPS Resource Monitoring - runs based on configured interval
Schedule::command('vps:monitor-resources')
    ->everyFifteenMinutes()
    ->when(function () {
        return Setting::get('resource_monitor_enabled', false);
    })
    ->withoutOverlapping()
    ->runInBackground();
