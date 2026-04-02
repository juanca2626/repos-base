<?php

namespace App\Console;

use App\Console\Commands\Statement;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Src\Modules\File\Infrastructure\Jobs\ProcessVoucherJob;
//use App\Console\Commands\SendVoucherNotification;  // Asegúrate de que el namespace sea correcto

class Kernel extends ConsoleKernel
{
    protected $commands = [
       // SendVoucherNotification::class,  // Asegúrate de que este comando esté registrado
       Statement::class
    ];
    protected function schedule(Schedule $schedule): void
    {
        //$schedule->command('voucher:send-notification')->hourly();
        // $schedule->command('app:statement')->everyMinute();  
        // $schedule->command('app:statement')->dailyAt('02:00');  
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
