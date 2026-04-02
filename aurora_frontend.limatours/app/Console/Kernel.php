<?php

namespace App\Console;

use App\Console\Commands\PendingPassengers;
use App\Console\Commands\ActiveRemindersDay;
use App\Console\Commands\ActiveRemindersWeek;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\PendingPassengers',
        'App\Console\Commands\ActiveRemindersDay',
        'App\Console\Commands\ActiveRemindersWeek',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('pending:passengers')->daily();
        $schedule->command('active:reminders:day')->daily(); // Diario..
        $schedule->command('active:reminders:week')->weekly(); // Semanal
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
