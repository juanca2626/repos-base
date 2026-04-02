<?php

namespace App\Providers;

use App\Jobs\ReservationHotelNotification;
use Illuminate\Support\ServiceProvider;

class ReservationHotelNotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindMethod(ReservationHotelNotification::class.'@handle', function ($job, $app) {
            return $job->handle($app->make(AudioProcessor::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
