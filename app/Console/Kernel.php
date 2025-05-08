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
        $schedule->command('app:sync-wilayah-command')->yearly();
        $schedule->command('app:site-map-command')->sundays();
        // $schedule->command('app:site-map-command')->sundays();
        // $schedule->command('app:charge-payment-xendit')->sundays();

        $schedule->command('app:charge-payment')->monthly();
        $schedule->command('app:charge-dpp-command')->monthly();
        // $schedule->command('app:check-transaction-old')->monthly();
        // $schedule->command('app:archive-charges')->yearly();
        // $schedule->command('app:up-class')->yearly();

        // $schedule->command('app:charge-payment')->everyMinute();
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
