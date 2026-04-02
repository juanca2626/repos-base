<?php

namespace App\Jobs;

use App\Mail\ReservationSummaryMail;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class SendSummaryReservationsEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Reservation */
    public $reservation;
    public $lang;

    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param  Reservation  $reservation
     * @param  bool  $isCancelation
     */
    public function __construct(Reservation $reservation, $lang = 'en')
    {
        $this->reservation = $reservation;
        $this->lang = $lang;
//        $this->connection = 'hotel_reservations_emails';
//        $this->queue = 'email_dispatcher';
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        $mailsData = $this->commit();

        $this->send($mailsData);
    }

    /**
     * @param  array  $mailsData
     * @throws \ReflectionException
     */
    public function send(array $mailsData)
    {

        //  echo json_encode($mailsData);
        // die('...');


        foreach ($mailsData as $mailData) {
//            throw new Exception(json_encode( $mailData )); die;

            $mailable = new $mailData['mail_class']($mailData['mail_data']);
            $mailable->subject($mailData['subject']);
            // echo $mailable->render();
            // echo "<br>".$mailData['subject'];
            // die('..'.$mailData['subject']);
            //dev
            $email = Mail::to($mailData['to']);
//            $email->cc(['jgq@limatours.com.pe']);

            //Produccion
//            $email = Mail::to($mailData['to']);
//            if ($mailData['cc']) {
//                $email->cc($mailData['cc']);
//            }

//            $email->bcc([
//                'jean@sga.la'
//            ]);

            $email->send($mailable);
            // echo $mailData['subject']."<br>";
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function commit()
    {

        $mailsData = [];
        $hotels_reservation = collect();
        $services_reservation = collect();

        //Todo Hotels
        foreach ($this->reservation->reservationsHotel as $reservationsHotel) {
            $hotels_reservation->add($reservationsHotel);
        }
        //Todo Services
        foreach ($this->reservation->reservationsService as $reservationsService) {
            $services_reservation->add($reservationsService);
        }

        if ($services_reservation->count() > 0 || $hotels_reservation->count() > 0) {
//            $codes_services = $services_reservation->pluck('service_code');
//            $codes_hotels = $hotels_reservation->pluck('hotel_code');
//            $codes = array();
//            if ($codes_hotels->count() > 0) {
//                $codes = $codes_hotels->merge($codes_services)->toArray();
//            } elseif ($codes_services->count() > 0) {
//                $codes = $codes_services->merge($codes_hotels)->toArray();
//            }

            if (App::environment('production')) {
                $subject = trans('mails.reservation.reservation_summary', [], $this->lang);
            } else {
                $subject = trans('mails.reservation.reservation_summary', [],
                        $this->lang).' - '.trans('mails.test_email', [], $this->lang);
            }

            $mailsData[] = [
                'to' => Auth::user()->email,
                'mail_class' => ReservationSummaryMail::class,
                'mail_data' => [
                    'mail_type' => 'summary',
                    'file_code' => $this->reservation->file_code,
                    'booking_code' => $this->reservation->booking_code,
                    'customer_name' => $this->reservation->customer_name,
                    'created_at' => $this->reservation->created_at,
                    'hotels' => $hotels_reservation->toArray(),
                    'services' => $services_reservation->toArray(),
                    'lang' => $this->lang,
                ],
                'subject' => $subject.' - ['.$this->reservation->booking_code.']',
            ];
        }

        return $mailsData;

    }
}
