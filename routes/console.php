<?php

use App\Console\Commands\TestConsole;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ResetVolumes;

Schedule::job(ResetVolumes::dispatch())->lastDayOfMonth("22:00");

Schedule::command(TestConsole::class)->daily();

