<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;
use App\Jobs\WeeklyTimesheetJob;

Schedule::job(new WeeklyTimesheetJob)->weeklyOn(7, '23:00'); 
// Runs every Sunday at 11 PM
