<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kapanan müzayedelerin kazananlarına e-posta (aktive etmek için sunucuda cron:
// * * * * * php artisan schedule:run  ve .env'de MAIL_* ayarları)
Schedule::command('app:kapananlari-bildir')->everyMinute();
