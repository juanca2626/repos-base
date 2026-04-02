<?php

namespace App\Console\Commands;

use App\Reservation;
use App\ReservationsHotel;
use App\ReservationsHotelsRatesPlansRooms;
use App\Http\Traits\Reservations;
use Illuminate\Console\Command;
use App\Http\Traits\Aurora3;

class CreateReservationRequestStella extends Command
{
    use Reservations, Aurora3;

    private $reservation = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stella:create-reservation-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea el json para el ws de reservation de stella';

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
        $reservation = Reservation::where('status_cron_job_reservation_stella',
            Reservation::STATUS_CRONJOB_CREATE_FILE)
            ->where('file_code', '!=', '0') // obviamos los que tengan como valor 0
            ->where('status_cron_job_error', Reservation::STATUS_CRONJOB_ERROR_FALSE)
            ->with('client')
            ->first(['id', 'executive_id']);

        // dd(Reservation::STATUS_CRONJOB_CREATE_FILE);

        if ($reservation) {

            $this->reservation = Reservation::getReservations([
                'reservation_id' => $reservation->id,
                'status_email' => Reservation::STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE,
            ], true);

            if ($this->reservation->reservationsHotel->count() > 0 or $this->reservation->reservationsService->count() > 0 or $this->reservation->reservationsFlight->count() > 0) {


                $errorDiscountAllotments = $this->reservation->errorValidateDiscountReserveHotel();
                //Todo: validamos que las reservas que estan confirmadas, se ayan descontado del inventario.

                if(!$errorDiscountAllotments){

                    //Todo Save on Channels
                    $channelRes = $this->saveOnChannels($this->reservation);

                    if ($channelRes['success']) {
                        foreach ($this->reservation->reservationsHotel as $reservationsHotels) {
                            foreach ($reservationsHotels->reservationsHotelRooms as $reservationsHotelRoom) {
                                if ($reservationsHotelRoom->onRequest === ReservationsHotelsRatesPlansRooms::ON_REQUEST) {
                                    $reservation_hotel = ReservationsHotel::find($reservationsHotels->id);
                                    $reservation_hotel->status = ReservationsHotel::STATUS_NOT_CONFIRMED;
                                    $reservation_hotel->save();
                                }
                            }
                        }

                        $maxConsecutiveHotel = $this->reservation->getConsecutiveHotelPrev();
                        $reservation->max_consecutive_reservations = $maxConsecutiveHotel;

                        //Todo Update status cron job
                        if ($this->reservation->client->ecommerce == 1) {
                            //Todo opcion esta habilitada solo para  los clientes de tipo Vista ó Masi opcionales
                            // es para la creacion de asiento contable.
                            $reservation->status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CREATE_ACCOUNTING_SEAT;
                        } else {
                            $reservation->status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CLOSE_PROCESS;
                        }
                        //Todo Activamos el cronjob para el envio de correo
                        $reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_RESERVE;
                        $reservation->status_cron_job_error = Reservation::STATUS_CRONJOB_ERROR_FALSE;
                        $reservation->save();
                    } else {
                        //Todo: ponemos el status del error del cronjob en true para que envie la notificacion a TI
                        // Se ejecutara el commando aurora:notification_error_reservation_stella
                        $reservation->status_cron_job_error = Reservation::STATUS_CRONJOB_ERROR_TRUE;
                        $reservation->save();
                    }

                }
            } else {
                //Todo Si no hay ningun servicio u hotel agregado terminamos el proceso de creacion de file y de envio de emails
                $reservation->status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CLOSE_PROCESS;
                $reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_CLOSE_PROCESS;
                $reservation->save();

                // Creamos file A3..
                $this->createFileA3($reservation);
            }
        }
    }
}
