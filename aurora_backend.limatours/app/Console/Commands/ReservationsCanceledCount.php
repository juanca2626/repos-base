<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReservationsCanceledCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations-canceled:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cuenta la cantidad de reservas canceladas para cada dia y las actualiza en el inventario';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $reservation = new \App\Reservation();
        $reservation->reservation_canceled_count();
    }
}
