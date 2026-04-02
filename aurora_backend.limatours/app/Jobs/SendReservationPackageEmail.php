<?php

namespace App\Jobs;

use App\ClientExecutive;
use App\ClientSeller;
use App\Contact;
use App\Mail\ReservationHotelClientMail;
use App\Mail\ReservationHotelExecutiveMail;
use App\Mail\ReservationHotelMail;
use App\Mail\ReservationPackageClientMail;
use App\Reservation;
use App\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReservationPackageEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Reservation */
    public $reservation;

    public $tries = 2;

    public $email_type;

    public $email;

    /**
     * Create a new job instance.
     *
     * @param  Reservation  $reservation
     * @param  string  $email
     * @param  string  $email_type
     */
    public function __construct(Reservation $reservation, $email, $email_type = 'commit')
    {
        $this->reservation = $reservation;
        $this->connection = 'hotel_reservations_emails';
        $this->queue = 'email_dispatcher';
        $this->email_type = $email_type;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
//        $this->email_type = $isCancelation ? 'cancel' : 'commit';
        switch ($this->email_type) {
            case 'commit':
                $mailsData = $this->commit();
                break;
            case 'modification':
                $mailsData = $this->modification();
                break;
            case 'cancel':
                $mailsData = $this->cancel();
                break;
            default:
                throw new \Exception('Invalid email_type = '.$this->email_type);
                break;
        }

        $this->send($mailsData);
    }

    /**
     * @param  array  $mailsData
     * @throws \ReflectionException
     */
    public function send(array $mailsData)
    {
//        Log::error(json_encode($mailsData));
//        dd('.......');
        foreach ($mailsData as $mailData) {
            /** @var Mailable $mailable */
            foreach ($mailData['mail_data']['hotels'] as $hotInd => $reservations_hotel) {
                $reservations_hotel['total_tax_and_services_amount'] = 0;
                foreach ($reservations_hotel['reservations_hotel_rooms'] as $roomInd => $reservations_hotel_room) {
                    $reservations_hotel_room['policies_cancellation'] = collect($reservations_hotel_room['policies_cancellation'] ? json_decode($reservations_hotel_room['policies_cancellation'],
                        true) : []);
                    $reservations_hotel_room['taxes_and_services'] = collect($reservations_hotel_room['taxes_and_services'] ? json_decode($reservations_hotel_room['taxes_and_services'],
                        true) : []);
                    $reservations_hotel_room['total_tax_and_services_amount'] = 0;

                    if (isset($reservations_hotel_room['taxes_and_services']['apply_fees'])) {
                        foreach ($reservations_hotel_room['taxes_and_services']['apply_fees'] as $apply_fee) {
                            foreach ($apply_fee as $item) {
                                $reservations_hotel_room['total_tax_and_services_amount'] += $item['total_amount'];
                            }
                        }
                    }

                    $reservations_hotel['total_tax_and_services_amount'] += $reservations_hotel_room['total_tax_and_services_amount'];
                    $reservations_hotel['reservations_hotel_rooms'][$roomInd] = $reservations_hotel_room;
                }

                $mailData['mail_data']['hotels'][$hotInd] = $reservations_hotel;
            }

            $mailable = new $mailData['mail_class']($mailData['mail_data']);
            $mailable->subject($mailData['subject']);

            //Todo Produccion
            $email = Mail::to($this->email);
            $email->bcc([
                'jgq@limatours.com.pe'
            ]);
            $email->send($mailable);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function commit()
    {

        $mailsData = [];
        $emailEjecutivo = [];
        $hotels = [];
        $services = [];
        $package = [];
        $mailsCodeHotelAndServices = [];

        /*
         * Todo: Verificamos si la reserva es de un paquete y si tiene datos en la tabla reservations_packages
         * entonces el email para el cliente cambia.
        */
        if ($this->reservation->entity === 'Package' and $this->reservation->reservationsPackage->count() > 0) {
            $package = $this->reservation->reservationsPackage->first()->toArray();
        }


        // si la reserva esta en onRequest
        $on_request = false;
        $reservas = $this->reservation->reservationsHotel->toArray();
        foreach ($reservas as $hotelReservation) {
            foreach ($hotelReservation['reservations_hotel_rooms'] as $resHotRoom) {
                if ($resHotRoom['channel_id'] == 1 and $resHotRoom['onRequest'] == 0) {
                    $on_request = true;
                    break;
                }
            }
        }

        //Todo Email para Clientes
        $mailsData[] = [
            'to' => $this->email,
            'mail_class' => ReservationPackageClientMail::class,
            'mail_data' => [
                'mail_type' => 'confirmation',
                'date_init' => $this->reservation->date_init,
                'total_amount' => $this->reservation->total_amount,
                'on_request' => $on_request,
                'client' => $this->reservation->client->toArray(),
                'file_code' => $this->reservation->file_code,
                'customer_name' => $this->reservation->customer_name,
                'created_at' => $this->reservation->created_at,
                'executive_email' => '',
                'hotels' => $this->reservation->reservationsHotel->toArray(),
                'services' => $this->reservation->reservationsService->toArray(),
                'package' => $package,
                'lang' => 'en',
            ],
            'subject' => 'Client Booking ['.$this->reservation->file_code.']',
        ];

        return $mailsData;

    }


    /**
     * @return array
     * @throws \Exception
     */
//    public function modification()
//    {
//
//        $mailsData = [];
//        $emailEjecutivo = [];
//
//        $client_id = $this->reservation->client_id;
//        $client_sellers_user_ids = $clients = ClientSeller::where('status', 1)
//            ->where('client_id', $client_id)
//            ->pluck('user_id');
//        $clientSellers = (count($client_sellers_user_ids) > 0) ? User::whereIn('id',
//            $client_sellers_user_ids)->pluck('email') : [];
//
////        $clientExecutives = $this->getExecutivesByClient($this->reservation->client_code);
//        $clientExecutives = $this->get_client_executives($client_id);
//
//        // email para Ejecutivos
//        foreach ($this->reservation->reservationsHotel->groupBy('executive_id') as $executive_id => $reservationsHotels) {
//            $executive = User::find($executive_id);
//
//            if (empty($executive->email)) {
//                throw new Exception('Selected executive '.$executive_id.' has not notification emails defined');
//            }
//
//            $emailEjecutivo[] = $executive->email;
//
//            $mailsData[] = [
//                'to' => $executive->email,
//                'cc' => $clientExecutives,
//                'mail_class' => ReservationHotelExecutiveMail::class,
//                'mail_data' => [
//                    'mail_type' => 'confirmation',
//                    'file_code' => $this->reservation->file_code,
//                    'customer_name' => $this->reservation->customer_name,
//                    'created_at' => $this->reservation->created_at,
//                    'hotels' => $reservationsHotels->toArray(),
//                    'lang' => 'en',
//                ],
//                'subject' => 'Executive Hotel Booking Modification ['.$this->reservation->file_code.']',
//            ];
//        }
//
////        if ($this->reservation->reservator_type == 'client') {
//
//        if (count($clientSellers) > 0) {
//            // Email para Clientes
//            $mailsData[] = [
//                'to' => array_shift($clientSellers),
//                'cc' => $clientExecutives,
//                'mail_class' => ReservationHotelClientMail::class,
//                'mail_data' => [
//                    'mail_type' => 'confirmation',
//                    'file_code' => $this->reservation->file_code,
//                    'customer_name' => $this->reservation->customer_name,
//                    'executive_email' => $executive->email,
//                    'created_at' => $this->reservation->created_at,
//                    'hotels' => $this->reservation->reservationsHotel->toArray(),
//                    'lang' => 'en',
//                ],
//                'subject' => 'Client Hotel Booking Modification ['.$this->reservation->file_code.']',
//            ];
//        }
//
////        }
//
//        // Email para Hoteles
//        foreach ($this->reservation->reservationsHotel->groupBy('hotel_id') as $hotel_id => $reservationsHotels) {
//            $contactHotel = Contact::select('email')
//                ->whereHotelId($hotel_id)->whereNotNull('email')
//                ->pluck('email')->toArray();
//
//            if (count($contactHotel) == 0) {
//                throw new Exception('Selected Hotel '.$hotel_id.' has not notification emails defined');
//            }
//
//            $emailContact = array_shift($contactHotel);
////            foreach ($reservationsHotels as $reservationsHotel) {
//
//            // -------
//            $hotels_ = $reservationsHotels->toArray();
//            $total_for_confirm = 0;
//            $total_confirm = 0;
//            foreach($hotels_ as $h){
//                foreach($h["reservations_hotel_rooms"] as $resHotRoom){
//                    if( isset($resHotRoom["status"]) and $resHotRoom["status"] !== 0 ){
//                        $total_for_confirm++;
//                        if($resHotRoom["onRequest"]==1){
//                            $total_confirm++;
//                        }
//                    }
//                }
//            }
//            if($total_confirm === $total_for_confirm){
//                $confirmed = 1;
//            } else {
//                $confirmed = 0;
//            }
//
//            $mailsData[] = [
//                'to' => $emailContact,
//                'cc' => $contactHotel,
//                'mail_class' => ReservationHotelMail::class,
//                'mail_data' => [
//                    'confirmed' => $confirmed,
//                    'mail_title' => 'Reserva Confirmada',
//                    'mail_type' => 'confirmation',
//                    'file_code' => $this->reservation->file_code,
//                    'customer_name' => $this->reservation->customer_name,
//                    'executive_email' => $executive->email,
//                    'created_at' => $this->reservation->created_at,
//                    'hotels' => $hotels_,
//                    'lang' => 'en',
//                ],
//                'subject' => 'Reserva Confirmada' . ' ['.$this->reservation->file_code.'] - '.$reservationsHotels[0]->hotel_code . '-' . $this->reservation->customer_name,
//            ];
////            }
//        }
//
//        return $mailsData;
//    }

    /**
     * @return array
     * @throws Exception
     */
//    public function cancel()
//    {
//
//        $client_id = $this->reservation->client_id;
//        $client_sellers_user_ids = $clients = ClientSeller::where('status', 1)
//            ->where('client_id', $client_id)
//            ->pluck('user_id');
//        $clientSellers = (count($client_sellers_user_ids) > 0) ? User::whereIn('id',
//            $client_sellers_user_ids)->pluck('email')->toArray() : [];
//
////        $clientExecutives = $this->getExecutivesByClient($this->reservation->client_code);
//        $clientExecutives = $this->get_client_executives($client_id);
//
//        $mailsData = [];
//        foreach ($this->reservation->reservationsHotel->groupBy('executive_id') as $executive_id => $reservationsHotels) {
//            $executive = User::find($executive_id);
//
//            if (empty($executive->email)) {
//                throw new Exception('Selected executive '.$executive_id.' has not notification emails defined');
//            }
//
//            $mailsData[] = [
//                'to' => $executive->email,
//                'cc' => $clientExecutives,
//                'mail_class' => ReservationHotelExecutiveMail::class,
//                'mail_data' => [
//                    'mail_type' => 'cancelation',
//                    'file_code' => $this->reservation->file_code,
//                    'customer_name' => $this->reservation->customer_name,
//                    'created_at' => $this->reservation->created_at,
//                    'hotels' => $reservationsHotels->toArray(),
//                    'lang' => 'en',
//                ],
//                'subject' => 'Executive Hotel Cancellation ['.$this->reservation->file_code.']',
//            ];
//        }
//
////        if ($this->reservation->reservator_type == 'client') {
//// INDICACIONES ESPECIFICAS QUE ESTO NO DEBE LLEGARLE A LOS CLIENTES
////            if (count($clientSellers) > 0) {
////                // Email para Clientes
////                $mailsData[] = [
////                    'to' => array_shift($clientSellers),
////                    'cc' => $clientExecutives,
////                    'mail_class' => ReservationHotelClientMail::class,
////                    'mail_data' => [
////                        'mail_type' => 'cancelation',
////                        'file_code' => $this->reservation->file_code,
////                        'customer_name' => $this->reservation->customer_name,
////                        'executive_email' => $executive->email,
////                        'created_at' => $this->reservation->created_at,
////                        'hotels' => $this->reservation->reservationsHotel->toArray(),
////                        'lang' => 'en',
////                    ],
////                    'subject' => 'Client Hotel Cancellation ['.$this->reservation->file_code.']',
////                ];
////            }
////        }
//        foreach ($this->reservation->reservationsHotel->groupBy('hotel_id') as $hotel_id => $reservationsHotels) {
//            $contactHotel = Contact::select('email')
//                ->whereHotelId($hotel_id)->whereNotNull('email')
//                ->pluck('email')->toArray();
//
//            if (count($contactHotel) == 0) {
//                throw new Exception('Selected Hotel '.$hotel_id.' has not notification emails defined');
//            }
//
//            $emailContact = array_shift($contactHotel);
////            foreach ($reservationsHotels as $reservationsHotel) {
//            $mailsData[] = [
//                'to' => $emailContact,
//                'cc' => $contactHotel,
//                'mail_class' => ReservationHotelMail::class,
//                'mail_data' => [
//                    'confirmed' => 2,
//                    'mail_title' => 'Cancelación de Reserva',
//                    'mail_type' => 'cancelation',
//                    'file_code' => $this->reservation->file_code,
//                    'customer_name' => $this->reservation->customer_name,
//                    'executive_email' => $executive->email,
//                    'created_at' => $this->reservation->created_at,
//                    'hotels' => $reservationsHotels->toArray(),
//                    'lang' => 'en',
//                ],
//                'subject' => 'Hotel Cancellation ['.$this->reservation->file_code.']',
//            ];
////            }
//        }
//
//        return $mailsData;
//    }

//    public function get_client_executives($client_id)
//    {
//
//        $client_executives = ClientExecutive::where('client_id', $client_id)
//            ->where('status', 1)
//            ->where('use_email_reserve', 1)
//            ->with('user')
//            ->get();
//
//        $client_executives_emails = [];
//
//        foreach ($client_executives as $client_executive) {
//            array_push($client_executives_emails, $client_executive->user->email);
//        }
//
//        return $client_executives_emails;
//    }
}
