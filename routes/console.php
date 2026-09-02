<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('leads:alert-no-followup')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('subscriptions:check-expiry')->dailyAt('06:00')->withoutOverlapping();
