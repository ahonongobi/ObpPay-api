<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Schedule the monthly installment processing
        // Execute installment processing daily at midnight
        // * * * * * php /var/www/html/obppay/artisan schedule:run >> /dev/null 2>&1
        $schedule->call(function () {
            \Illuminate\Support\Facades\Http::post(env('APP_URL') . '/api/cron/installments/process');
        })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
