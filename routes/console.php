<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule::command('backup:run')
//     ->dailyAt('02:00')
//     ->withoutOverlapping()
//     ->onSuccess(function () {
//         Log::channel('backup')->info('Backup completed successfully');
//     })
//     ->onFailure(function () {
//         Log::channel('backup')->error('Backup failed');
//     });
