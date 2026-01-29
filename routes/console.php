<?php

use App\Jobs\ProcessDripCampaigns;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process drip email campaigns every hour
Schedule::job(new ProcessDripCampaigns)->hourly();
