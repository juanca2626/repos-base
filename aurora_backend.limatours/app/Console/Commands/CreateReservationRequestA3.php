<?php

namespace App\Console\Commands;

use App\Reservation;
use App\ReservationsHotel;
use App\ReservationsHotelsRatesPlansRooms;
use App\Http\Traits\Reservations;
use Illuminate\Console\Command;

class CreateReservationRequestA3 extends Command
{
    use Reservations;

    private $reservation = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'a3:create-reservation-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea el json para el ws de reservation de A3';

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
        //Todo Buscamos las reservas que estan en estado 2 para la creacion de file
        $reservation = Reservation::where('status_cron_job_reservation_a3',
            Reservation::STATUS_CRONJOB_CREATE_FILE)
            ->where('file_code', '!=', '0') // obviamos los que tengan como valor 0
            ->where('status_cron_job_error_a3', Reservation::STATUS_CRONJOB_ERROR_FALSE)
            ->with('client')
            ->first(['id']);
        if ($reservation) {

            $this->reservation = Reservation::getReservations([
                'reservation_id' => $reservation->id,
                // 'status_email' => Reservation::STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE,
            ], true);

            if ($this->reservation->reservationsHotel->count() > 0 or $this->reservation->reservationsService->count() > 0 or $this->reservation->reservationsFlight->count() > 0) {
                //Todo Save on Channels
                $channelRes = $this->saveOnChannels($this->reservation, 'a3');
                if ($channelRes['success']) {
                    //Todo Update status cron job
                    if ($this->reservation->client->ecommerce == 1) {
                        //Todo opcion esta habilitada solo para  los clientes de tipo Vista ó Masi opcionales
                        // es para la creacion de asiento contable.
                        $reservation->status_cron_job_reservation_a3 = Reservation::STATUS_CRONJOB_CREATE_ACCOUNTING_SEAT;
                    } else {
                        $reservation->status_cron_job_reservation_a3 = Reservation::STATUS_CRONJOB_CLOSE_PROCESS;
                    }
                    //Todo Activamos el cronjob para el envio de correo
                    // $reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_RESERVE;
                    $reservation->status_cron_job_error_a3 = Reservation::STATUS_CRONJOB_CLOSE_PROCESS;
                    $reservation->save();
                } else {
                    //Todo: ponemos el status del error del cronjob en true para que envie la notificacion a TI
                    // Se ejecutara el commando aurora:notification_error_reservation_stella
                    $reservation->status_cron_job_error_a3 = Reservation::STATUS_CRONJOB_ERROR_TRUE;
                    $reservation->save();
                }
            } else {
                //Todo Si no hay ningun servicio u hotel agregado terminamos el proceso de creacion de file y de envio de emails
                $reservation->status_cron_job_reservation_a3 = Reservation::STATUS_CRONJOB_CLOSE_PROCESS;
                // $reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_CLOSE_PROCESS;
                $reservation->save();
            }
        }
    }
}
