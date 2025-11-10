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

        // Generate recurring addons invoices daily at 06:10 AM
        $schedule->command('invoices:generate-recurring-addons')
                 ->dailyAt('06:10')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Tandai overdue (tanpa cancel) setiap jam
        $schedule->command('invoices:mark-overdue')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Suspend overdue projects daily at 7:00 AM
        $schedule->command('projects:suspend-overdue')
                 ->dailyAt('07:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Auto-cancel overdue add-ons daily at 07:10 AM
        $schedule->command('addons:cancel-overdue')
                 ->dailyAt('07:10')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Apply cancel-at-period-end daily at 07:15 AM
        $schedule->command('addons:apply-cancel-at-period-end')
                 ->dailyAt('07:15')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Pengingat invoice harian pukul 08:00
        $schedule->command('invoices:send-reminders')
                 ->dailyAt('08:00')
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