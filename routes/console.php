<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batalkan pesanan yang lewat batas bayar + kembalikan stok. Butuh cron: * * * * * php artisan schedule:run
Schedule::command('orders:release-expired')->everyMinute()->withoutOverlapping();
Schedule::command('reviews:send-reminders')->hourly()->withoutOverlapping();