<?php

namespace App\Console\Commands;

use App\Jobs\SendHotelReservationsEmails;
use App\Reservation;
use App\ReservationsFlight;
use App\ReservationsHotel;
use App\ReservationsService;
use App\Http\Traits\Reservations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\Aurora3;

class SendEmailReservation extends Command
{
    use Reservations, Aurora3;

    private $reservation = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stella:send-email-reservation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia el email de las reservas';

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
     * php artisan queue:work hotel_reservations_emails --queue=email // Para ejecutar la cola en local
     * @return mixed
     */
    public function handle()
    {
        //Todo Buscamos las reservas que estan en estado 1 para envio de correo
        $reservation = Reservation::where('status_cron_job_send_email',
            Reservation::STATUS_CRONJOB_SEND_EMAIL_RESERVE)
            ->where('status_cron_job_error', Reservation::STATUS_CRONJOB_ERROR_FALSE)
            ->where('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CLOSE_PROCESS)
            ->first(['id', 'executive_id']);
        if ($reservation) {
            $this->reservation = Reservation::getReservations([
                'reservation_id' => $reservation->id,
                'status_email' => Reservation::STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE,
            ], true);

            if ($this->reservation->reservationsHotel->count() > 0 or $this->reservation->reservationsService->count() > 0 or $this->reservation->reservationsFlight->count() > 0) {

                //Todo: Se envia los correos
                $validateSendMail = $this->reservation->reservationsHotel->first();

                if(isset($validateSendMail) and $validateSendMail){
                    if($validateSendMail->send_mail == "1"){
                        SendHotelReservationsEmails::dispatchNow($this->reservation);
                    }
                }else{
                    //add validation send email by only services
                    $validateSendMail2 = $this->reservation->reservationsService->first();

                    if(isset($validateSendMail2) and $validateSendMail2){
                        if($validateSendMail2->status_email == "0"){
                            SendHotelReservationsEmails::dispatchNow($this->reservation);
                        }
                    }
                }

                //Todo Hoteles : Actulizado el estado del email
                foreach ($this->reservation->reservationsHotel as $reservationsHotels) {
                    $reserveHotel = ReservationsHotel::find($reservationsHotels->id);
                    $reserveHotel->status_email = ReservationsHotel::STATUS_EMAIL_SENT;
                    $reserveHotel->save();
                }

                //Todo Servicios: Actulizado el estado del email
                foreach ($this->reservation->reservationsService as $reservationsServices) {
                    $reserveService = ReservationsService::find($reservationsServices->id);
                    $reserveService->status_email = ReservationsService::STATUS_EMAIL_SENT;
                    $reserveService->save();
                }

                //Todo Vuelos: Actulizado el estado del email
                foreach ($this->reservation->reservationsFlight as $reservationsFlight) {
                    $reserveFlight = ReservationsFlight::find($reservationsFlight->id);
                    $reserveFlight->status_email = ReservationsFlight::STATUS_EMAIL_SENT;
                    $reserveFlight->save();
                }

            }
            $reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_CLOSE_PROCESS;
            $reservation->save();

            // Creamos FILE A3..
            $this->createFileA3($reservation);
        }
    }
}
