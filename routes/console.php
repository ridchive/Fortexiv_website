<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$midtransCheckInterval = (int) config('services.midtrans.status_check_interval_minutes');

if ($midtransCheckInterval > 0) {
    Schedule::command('marketplace:sync-midtrans-payments')
        ->cron($midtransCheckInterval < 60
            ? "*/{$midtransCheckInterval} * * * *"
            : '0 */'.min(24, intdiv($midtransCheckInterval, 60)).' * * *')
        ->withoutOverlapping();
}
