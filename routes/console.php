<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:process')->everyMinute();
Schedule::command('notifications:process-scheduled')->everyMinute();
Schedule::command('appointments:remind')->everyFiveMinutes();
Schedule::command('sepay:sync')->everyFiveMinutes();
