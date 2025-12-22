<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Process recurring tasks every hour
        $schedule->command('tasks:process-recurring')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Sync AdSense data daily at 4 AM (data from 2 days ago)
        $schedule->command('adsense:sync')
                 ->dailyAt('04:00')
                 ->withoutOverlapping();

        // Assign new jobs to users every hour based on their countries
        $schedule->command('jobs:assign-new')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
