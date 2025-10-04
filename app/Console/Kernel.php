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
        // Generate recurring invoices daily at 6:00 AM
        $schedule->command('invoices:generate-recurring')
                 ->dailyAt('06:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Cancel expired invoices every hour
        $schedule->command('invoices:cancel-expired')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Suspend overdue projects daily at 7:00 AM
        $schedule->command('projects:suspend-overdue')
                 ->dailyAt('07:00')
                 ->withoutOverlapping()
                 ->runInBackground();
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