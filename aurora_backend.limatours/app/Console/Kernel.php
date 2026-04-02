<?php

namespace App\Console;

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
        'App\Console\Commands\ReservationsActiveCount',
        'App\Console\Commands\ReservationsCanceledCount',
        'App\Console\Commands\CreateClientStellaService',
        'App\Console\Commands\CreateReservationRequestStella',
        'App\Console\Commands\SendEmailReservation',
        'App\Console\Commands\updateCloudinaryHotels',
        'App\Console\Commands\updateCloudinaryServices',
        'App\Console\Commands\NotificationReminder',
        'App\Console\Commands\CheckRatesInPackages',
        'App\Console\Commands\AddClientMarkups',
        'App\Console\Commands\deleteRateHistoriesHotel',
        'App\Console\Commands\cleanRateHotels',
        'App\Console\Commands\updateDateRangesHotels',
        'App\Console\Commands\CreateOrUpdateDateRangeHotel',
        'App\Console\Commands\cleanRoomsDisable',
        'App\Console\Commands\OrderServiceInclusions',
        'App\Console\Commands\ResetPasswordAdmin',
        'App\Console\Commands\DisableServicesClientMasi',
        'App\Console\Commands\AddMarkupServicesClientMasi',
        'App\Console\Commands\CheckDeactivatables',
        'App\Console\Commands\DisableOrEnablePromotionRates',
        'App\Console\Commands\AddLinkTripAdvisorServices',
        'App\Console\Commands\SendEmailTripAdvisorPassengers',
        // Modularizado por Alex Q.
        'App\Http\Multichannel\Hyperguest\Console\Commands\HotelSyncCommand',
        'App\Http\Multichannel\Hyperguest\Console\Commands\ImportHyperguestStaticHotels',
        'App\Console\Commands\AnonymizeAurora',
        'App\Console\Commands\ExportRolesPermissions',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
