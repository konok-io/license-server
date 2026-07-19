<?php

declare(strict_types=1);

use App\Console\Commands\ExpireLicenses;
use App\Console\Commands\PurgeStaleActivations;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Schedule
|--------------------------------------------------------------------------
| Server-side maintenance that runs independently of client heartbeats.
| Enable with a single system cron entry:
|   * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
*/

Schedule::command(ExpireLicenses::class)->dailyAt('00:15');
Schedule::command(PurgeStaleActivations::class)->dailyAt('00:30');
