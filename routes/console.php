<?php

use App\Jobs\ProcessDripCampaigns;
use App\Models\Otp;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send daily task reminder emails at 7 AM
// Sends consolidated email with today's tasks and overdue items
Schedule::command('reminders:send-daily')->dailyAt('07:00');

// Process drip email campaigns daily at 9 AM
Schedule::job(new ProcessDripCampaigns)->dailyAt('09:00');

// Send subscription renewal reminder emails daily at 8 AM
// Sends reminders 7 days, 3 days, and 0 days before subscription expiry
Schedule::command('subscriptions:send-reminders')->dailyAt('08:00');

// Send admin daily digest email at 8:30 AM
// Includes new sign-ups, today's payments, and pending payments
Schedule::command('admin:send-daily-digest')->dailyAt('08:30');

// Clean up expired OTPs older than 24 hours
Schedule::call(function () {
    Otp::where('created_at', '<', now()->subHours(24))->delete();
})->daily()->description('Clean up expired OTPs older than 24 hours');
