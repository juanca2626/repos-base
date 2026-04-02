<?php

namespace App\Console\Commands;

use App\Jobs\SendHotelReservationsEmails;
use App\Reservation;
use App\ReservationsHotel;
use App\ReservationsHotelsRatesPlansRooms;
use App\Http\Traits\Reservations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateEmailReservation extends Command
{
    use Reservations;

    private $reservation = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stella:create-email-reservation {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea el email de reserva';

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
        //Todo Buscamos las reservas que estan en estado 1 para la creacion de file
        $file = $this->argument('file');
        $reservation = Reservation::where('file_code',$file)->with('client')->first(['id']);
        if ($reservation) {
            $this->reservation = Reservation::getReservations([
                'reservation_id' => $reservation->id,
            ], true);
            //Todo Send Emails
            SendHotelReservationsEmails::dispatchNow($this->reservation);
        }
    }
}
