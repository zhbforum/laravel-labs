<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})
    ->purpose("Display an inspiring quote")
    ->hourly();

Schedule::command("app:check-expired-tasks")->dailyAt("00:30");

Schedule::command("app:generate-report --store-file")->weeklyOn(1, "09:00");
