<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReservationsActiveCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations-active:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cuenta la cantidad de reservas activas para cada dia y las actualiza en el inventario';

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
        $date_validate = date('Y-m-d');
        $date_validate = $this->quitarDias($date_validate, 15);

        $reservation = new \App\Reservation();
        $reservation->reservations_active_count($date_validate);
    }

    public function quitarDias($fecha, $dias)
    {
        $fec_vencimi = date("Y-m-d", strtotime("$fecha - $dias days"));
        return $fec_vencimi;
    }


    public function agregaDias($fecha, $dias)
    {
        $fec_vencimi = date("Y-m-d", strtotime("$fecha + $dias days"));
        return $fec_vencimi;
    }
}
