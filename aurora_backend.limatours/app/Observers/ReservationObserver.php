<?php

namespace App\Observers;

use App\Reservation;
use App\Jobs\WebhookN8nCreateFile;
use Illuminate\Support\Facades\Log;

class ReservationObserver
{
    /**
     * Handle the reservation "created" event.
     *
     * @param \App\Reservation $reservation
     * @return void
     */
    public function created(Reservation $reservation)
    {
        // Solo ejecutar en entorno de producción
        if ($reservation->entity === 'Stella' || !app()->environment('production')) {
            return;
        }
        WebhookN8nCreateFile::dispatch($reservation)->onQueue('n8n_create_file');;
    }

    /**
     * Handle the reservation "updated" event.
     *
     * @param \App\Reservation $reservation
     * @return void
     */
    public function updated(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "deleted" event.
     *
     * @param \App\Reservation $reservation
     * @return void
     */
    public function deleted(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "restored" event.
     *
     * @param \App\Reservation $reservation
     * @return void
     */
    public function restored(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "force deleted" event.
     *
     * @param \App\Reservation $reservation
     * @return void
     */
    public function forceDeleted(Reservation $reservation)
    {
        //
    }
}
