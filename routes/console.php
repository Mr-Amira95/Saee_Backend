<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:auto-checkout')->dailyAt('00:00');

Schedule::command('waybills:prune --days=7')->dailyAt('03:00');

Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
