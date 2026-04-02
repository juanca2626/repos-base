<?php

namespace App\Jobs;

use App\Client;
use App\ClientExecutive;
use App\ClientSeller;
use App\Contact;
use App\Country;
use App\Http\Stella\StellaService;
use App\Mail\ReservationHotelClientMail;
use App\Mail\ReservationHotelExecutiveMail;
use App\Mail\ReservationHotelMail;
use App\Mail\ReservationPackageClientMail;
use App\Mail\PartialCancelationMail;
use App\Reservation;
use App\ReservationsEmailsLog;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\SqsNotification;
use Throwable;

class SendHotelReservationsEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SqsNotification;

    /** @var Reservation */
    public $reservation;

    public $tries = 2;

    public $email_type;
    public $comment;
    public $block_email_provider;
    public $channel_cancel_by_rooms_hyperguest;

    /**
     * Create a new job instance.
     *
     * @param  Reservation  $reservation
     * @param  bool  $isCancelation
     */
    public function __construct(Reservation $reservation, $email_type = 'commit', $comment = '', $block_email_provider = '', $channel_cancel_by_rooms_hyperguest = '')
    {
        $this->reservation = $reservation;
        $this->connection = 'hotel_reservations_emails';
        $this->queue = 'email_dispatcher';
        $this->email_type = $email_type;
        $this->comment = $comment;
        $this->block_email_provider = $block_email_provider;
        $this->channel_cancel_by_rooms_hyperguest = $channel_cancel_by_rooms_hyperguest;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        switch ($this->email_type) {
            case 'commit':
                $mailsData = $this->commit();
                break;
            case 'modification':
                $mailsData = $this->modification();
                break;
            case 'cancel_partial':
                $mailsData = $this->cancel_partial();
                break;
            case 'cancel':
                $mailsData = $this->cancel();
                break;
            default:
                throw new \Exception('Invalid email_type = ' . $this->email_type);
                break;
        }

        $this->send($mailsData);
    }

    /**
     * @param array $mailsData
     * @throws Exception|Throwable
     */
    public function send(array $mailsData)
    {
        try {

            foreach ($mailsData as $mailData) {
                /** @var Mailable $mailable */
                foreach ($mailData['mail_data']['hotels'] as $hotInd => $reservations_hotel) {
                    $reservations_hotel['total_tax_and_services_amount'] = 0;
                    foreach ($reservations_hotel['reservations_hotel_rooms'] as $roomInd => $reservations_hotel_room) {
                        $reservations_hotel_room['policies_cancellation'] = collect($reservations_hotel_room['policies_cancellation'] ? json_decode(
                            $reservations_hotel_room['policies_cancellation'],
                            true
                        ) : []);
                        $reservations_hotel_room['taxes_and_services'] = collect($reservations_hotel_room['taxes_and_services'] ? json_decode(
                            $reservations_hotel_room['taxes_and_services'],
                            true
                        ) : []);
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

                // Envia notificaciones por brevo
                $mailable = new $mailData['mail_class']($mailData['mail_data']);
                $mailable->subject($mailData['subject']);

                if (App::environment('production')) {
                    //Todo Produccion
                    $email = Mail::to($mailData['to']);
                    if ($mailData['cc']) {
                        $email->cc($mailData['cc']);
                    }
                    $email->bcc([
                        'admin@limatours.com.pe',
                    ]);
                    $email->send($mailable);
                }

                /////////////////////////////////////////////////////////////////////////////////////////
                // Envia notificaciones por aws
                $html = view($mailData['mail_data']['template'], ['reservation' => $mailData['mail_data']])->render();

                $params = [
                    'template' => 'reservation',
                    'module' => 'aurora back / reservation',
                    'submodule' => $mailData['mail_data']['mail_type'],
                    'object_id' => $mailData['mail_data']['file_code'],
                    'replyTo' => [],
                    'subject' => $mailData['subject'],
                    'body' => $html,
                    'user' => $mailData['mail_data']['executive_code'],
                    'attachments' => [],
                    'data' => [
                        'mail_type' => $mailData['mail_data']['mail_type'],
                        'mail_config_to' => $mailData['mail_data']['mail_config_to'],
                        'hotel_code' => $mailData['mail_data']['hotel_code'],
                        'hotels' => $this->getCodeHotels($mailData['mail_data']['hotels']),
                        'services' => $this->getCodeServices($mailData['mail_data']['services']),
                    ]
                ];

                if (App::environment('production')) {
                    //Todo Produccion
                    $params['to'] = [$mailData['to']];
                    $params['cc'] = $mailData['cc'];
                    $params['bcc'] = [
                        'admin@limatours.com.pe',
                        'jgq@limatours.com.pe',
                    ];
                } else {
                    $params['to'] = ['jgq@limatours.com.pe'];
                    $params['cc'] = [];
                    $params['bcc'] = [];
                }

                $this->send_notification($params);
                ///////////////////////////////////////////////////////////////////////////////////////


            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCodeHotels($hotels = [])
    {
        $codes = [];
        foreach ($hotels as $hotel) {
            if (isset($hotel['hotel_code'])) {
                array_push($codes, $hotel['hotel_code']);
            }
        }

        return $codes;
    }

    public function getCodeServices($services = [])
    {
        $codes = [];
        foreach ($services as $service) {
            if (isset($service['service_code'])) {
                array_push($codes, $service['service_code']);
            }
        }

        return $codes;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function commit()
    {

        $mailsData = [];
        $emailEjecutivo = [];
        $services = [];
        $package = [];
        $mailsCodeHotelAndServices = [];
        $emailClientPackageAvail = false;

        /*
         * Todo: Verificamos si la reserva es de un paquete y si tiene datos en la tabla reservations_packages
         *  entonces el email para el cliente cambia.
        */
        if ($this->reservation->entity === 'Package' and $this->reservation->reservationsPackage->count() > 0) {
            $emailClientPackageAvail = true;
            $package = $this->reservation->reservationsPackage->first()->toArray();
        }

        //Todo: se recorre los hoteles
        foreach ($this->reservation->reservationsHotel->groupBy('executive_id') as $executive_id => $reservationsHotels) {

            $executive = User::find($executive_id);

            if (empty($executive->email)) {
                throw new Exception('Selected executive ' . $executive_id . ' has not notification emails defined');
            }

            $emailEjecutivo[] = $executive->email;

            foreach ($reservationsHotels as $reservationsHotel) {
                $mailsCodeHotelAndServices[] = $reservationsHotel->hotel_code;
            }
        }

        //Todo: se recorre los servicios
        foreach ($this->reservation->reservationsService->groupBy('executive_id') as $executive_id => $reservationsServices) {
            $executive = User::find($executive_id);

            if (empty($executive->email)) {
                throw new Exception('Selected executive ' . $executive_id . ' has not notification emails defined');
            }

            $emailEjecutivo[] = $executive->email;
            foreach ($reservationsServices as $reservationsService) {
                $mailsCodeHotelAndServices[] = $reservationsService->service_code;
            }
            $services = $reservationsServices->toArray();
        }

        $client_id = $this->reservation->client_id;

        //Todo Obtenemos los correos de las ejecutvias que tiene asignado el cliente

        $clientKamExecutive = [];
        $executives = [];
        //Todo Si la reserva lo realiza un cliente le debe llegar el correo a las KAMS y ejecutivas
        if ($this->reservation->reservator_type === 'client') {
            $executives = $this->get_client_executives($client_id);
            //Todo Obtenemos los correos de las kams por el codigo del cliente
            $clientKamExecutive = $this->get_client_kams_executives($this->reservation->client_code);
            $clientExecutives = array_merge($executives, $clientKamExecutive);
        } else {
            $clientExecutives = $executives;
        }


        //Todo: Se adjunto los codigos de hoteles y servicios en el asunto del correo
        $mailsCodeHotelAndServices = implode(",", $mailsCodeHotelAndServices);

        //Todo Email para Ejecutivas
        $mailsData[] = [
            'to' => $executive->email,
            'cc' => $clientExecutives,
            'mail_class' => ReservationHotelExecutiveMail::class,
            'mail_data' => [
                'mail_type' => 'confirmation',
                'mail_config_to' => 'executive',
                'file_code' => $this->reservation->file_code,
                'customer_name' => $this->reservation->customer_name,
                'created_at' => $this->reservation->created_at,
                'hotels' => $this->reservation->reservationsHotel->toArray(),
                'executive_code' => isset($executive->code) ? $executive->code : '',
                'hotel_code' => '',
                'services' => $services,
                'lang' => 'en',
                'template' => 'emails.reservations.hotel.executive'
            ],
            'subject' => 'Executive Booking [' . $this->reservation->file_code . '] - ' . $mailsCodeHotelAndServices,
        ];

        $email_executives = [
            'to' => (!empty($executive->email)) ? [$executive->email] : [],
            'cc' => (count($executives) > 0) ? $executives : []
        ];

        //Todo Guardamos lo losg de los emails de las ejecutivas
        $this->saveEmailsLogs($this->reservation->id, $email_executives, ReservationsEmailsLog::EMAIL_TO_EXECUTIVE);

        //Todo Si existen Guardamos lo losg de los emails de las KAMS
        if (count($clientKamExecutive) > 0) {
            $email_kams = [
                'to' => $clientKamExecutive
            ];
            $this->saveEmailsLogs($this->reservation->id, $email_kams, ReservationsEmailsLog::EMAIL_TO_KAM);
        }

        // si la reserva esta en onRequest
        $on_request = false;
        $reservas = $this->reservation->reservationsHotel->toArray();
        foreach ($reservas as $hotelReservation) {
            foreach ($hotelReservation['reservations_hotel_rooms'] as $resHotRoom) {
                if ($resHotRoom['channel_id'] == 1 and $resHotRoom['onRequest'] == 0) {
                    $on_request = true;
                }
            }
        }

        if ($this->reservation->reservator_type === 'client') {

            $clientSellers = User::where('id', $this->reservation->create_user_id)->get(['email']);

            //Todo Email para Clientes
            $mailsData[] = [
                'to' => $clientSellers->pluck('email'),
                'cc' => $clientExecutives,
                'mail_class' => (!$emailClientPackageAvail) ? ReservationHotelClientMail::class : ReservationPackageClientMail::class,
                'mail_data' => [
                    'mail_type' => 'confirmation',
                    'mail_config_to' => 'client',
                    'date_init' => $this->reservation->date_init,
                    'total_amount' => $this->reservation->total_amount,
                    'on_request' => $on_request,
                    'client' => $this->reservation->client->toArray(),
                    'file_code' => $this->reservation->file_code,
                    'customer_name' => $this->reservation->customer_name,
                    'created_at' => $this->reservation->created_at,
                    'executive_code' => isset($executive->code) ? $executive->code : '',
                    'executive_name' => $executive->name,
                    'executive_email' => $executive->email,
                    'hotels' => $this->reservation->reservationsHotel->toArray(),
                    'hotel_code' => '',
                    'services' => $this->reservation->reservationsService->toArray(),
                    'package' => $package,
                    'lang' => 'en',
                    'template' => (!$emailClientPackageAvail) ? 'emails.reservations.hotel.client' : 'emails.reservations.package.client'
                ],
                'subject' => 'Client Booking [' . $this->reservation->file_code . ']',
            ];

            $email_clients = [
                'to' => (count($clientSellers) > 0) ? $clientSellers->pluck('email') : [],
                'cc' => (count($clientExecutives) > 0) ? $clientExecutives : []
            ];

            //Todo Guardamos lo losg de los emails de los clientes
            $this->saveEmailsLogs($this->reservation->id, $email_clients, ReservationsEmailsLog::EMAIL_TO_CLIENT);
        }

        //Todo Email para Hoteles
        foreach ($this->reservation->reservationsHotel->groupBy('hotel_id') as $hotel_id => $reservationsHotels) {
            $contactHotel = Contact::select('email')
                ->whereHotelId($hotel_id)->whereNotNull('email')->distinct()
                ->pluck('email')->toArray();

            if (count($contactHotel) == 0) {
                throw new Exception('Selected Hotel ' . $hotel_id . ' has not notification emails defined');
            }

            $emailContact_emails = $contactHotel;
            $emailContact = array_shift($contactHotel);
            $contactHotel = array_merge($contactHotel, $emailEjecutivo);

            // ---------
            $client_ = Client::find($this->reservation->client_id);
            $client_country_name = "";
            if ($client_->country_id !== null and $client_->country_id !== "") {
                $client_country = Country::where('id', $client_->country_id)
                    ->with(['translations' => function ($query) {
                        $query->where('language_id', 1);
                    }])
                    ->first();
                if ($client_country) {
                    $client_country_name = $client_country->translations[0]->value;
                }
            }

            // -------
            $hotels_ = $reservationsHotels->toArray();
            $total_for_confirm = 0;
            $total_confirm = 0;
            $is_modification = 0;
            foreach ($hotels_ as $h) {
                foreach ($h["reservations_hotel_rooms"] as $resHotRoom) {
                    if (isset($resHotRoom["status"]) and $resHotRoom["status"] !== 0) {
                        $total_for_confirm++;
                        if ($resHotRoom["onRequest"] == 1) {
                            $total_confirm++;
                        }
                    }
                    if ($resHotRoom["is_modification"] != 0) {
                        $is_modification++;
                    }
                }
            }
            if ($total_confirm === $total_for_confirm) {
                $confirmed = 1;  // Todos son OK
                $mail_title = 'Reserva confirmada';
            } else {
                $mail_title = 'Solicitud de reserva';
                if ($total_confirm === 0) {
                    $confirmed = 4; // Todos son RQ
                } else {
                    $confirmed = 0;
                }
            }
            if ($is_modification > 0) {
                $mail_title = 'Modificación de reserva';
            }

            //Todo Email para Hoteles
            $mailsData[] = [
                'to' => $emailContact,
                'cc' => $contactHotel,
                'mail_class' => ReservationHotelMail::class,
                'mail_data' => [
                    'confirmed' => $confirmed,
                    'mail_config_to' => 'hotel',
                    'mail_title' => $mail_title,
                    'mail_type' => 'confirmation',
                    'file_code' => $this->reservation->file_code,
                    'customer_name' => $this->reservation->customer_name,
                    'customer_country' => $client_country_name,
                    'executive_code' => isset($executive->code) ? $executive->code : '',
                    'executive_email' => $executive->email,
                    'created_at' => $this->reservation->created_at,
                    'hotels' => $hotels_,
                    'hotel_code' =>  $reservationsHotels[0]->hotel_code,
                    'services' => [],
                    'comment' => $this->comment,
                    'lang' => 'en',
                    'template' => 'emails.reservations.hotel'
                ],
                'subject' => $mail_title . ' [' . $this->reservation->file_code . '] - ' . $reservationsHotels[0]->hotel_code . '-' . $this->reservation->customer_name,
            ];

            $email_hotels = [
                'to' => [$emailContact],
                'cc' => (count($contactHotel) == 0) ? [] : $contactHotel
            ];
            //Todo Guardamos lo losg de los emails de los hoteles
            $this->saveEmailsLogs($this->reservation->id, $email_hotels, ReservationsEmailsLog::EMAIL_TO_HOTEL);
        }

        return $mailsData;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function cancel_partial()
    {
        $mailsData = [];
        $emailEjecutivo = [];

        $client_id = $this->reservation->client_id;
        if ($this->reservation->reservator_type = "client") {
            //Todo Obtenemos los correos de las ejecutvias que tiene asignado el cliente
            $executives = $this->get_client_executives($client_id);
            $clientKamExecutive = [];
            //Todo Si la reserva lo realiza un cliente le debe llegar el correo a las KAMS y ejecutivas
            if ($this->reservation->reservator_type === 'client') {
                //Todo Obtenemos los correos de las kams por el codigo del cliente
                $clientKamExecutive = $this->get_client_kams_executives($this->reservation->client_code);
                $clientExecutives = array_merge($executives, $clientKamExecutive);
            } else {
                $clientExecutives = $executives;
            }
        }
        if ($this->reservation->reservator_type = "excecutive") {

            $clientExecutives = [];
        }


        // extraer montos con penalidad
        $this->get_penalities();

        //***
        $client_ = Client::find($this->reservation->client_id);
        $client_country_name = "";
        if ($client_->country_id !== null and $client_->country_id !== "") {
            $client_country = Country::where('id', $client_->country_id)
                ->with(['translations' => function ($query) {
                    $query->where('language_id', 1);
                }])
                ->first();
            if ($client_country) {
                $client_country_name = $client_country->translations[0]->value;
            }
        }
        //****
        $mail_title = "Cancelación parcial de reserva";
        foreach ($this->reservation->reservationsHotel as $reservationsHotel) {
            foreach ($reservationsHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                if ($reservationsHotelRoom->onRequest == "0") {
                    $mail_title = "Desestimación de solicitud de reserva parcial";
                }
            }
        }

        // email para Ejecutivos
        foreach ($this->reservation->reservationsHotel->groupBy('executive_id') as $executive_id => $reservationsHotels) {
            $executive = User::find($executive_id);
            if (empty($executive->email)) {
                throw new Exception('Selected executive ' . $executive_id . ' has not notification emails defined');
            }
            $emailEjecutivo[] = $executive->email;
        }

        $mailsData[] = [
            'to' => $executive->email,
            'cc' => $clientExecutives,
            'mail_class' => PartialCancelationMail::class,
            'mail_data' => [
                'confirmed' => 3,
                'mail_title' => $mail_title,
                'mail_type' => 'ANULACION PARCIAL',
                'mail_config_to' => 'executive',
                'comment' => $this->comment,
                'file_code' => $this->reservation->file_code,
                'customer_name' => $this->reservation->customer_name,
                'customer_country' => $client_country_name,
                'executive_code' => isset($executive->code) ? $executive->code : '',
                'executive_email' => $executive->email,
                'created_at' => $this->reservation->created_at,
                'hotels' => $reservationsHotels->toArray(),
                'hotel_code' =>  $reservationsHotels[0]->hotel_code,
                'services' => [],
                'lang' => 'en',
                'template' => 'emails.reservations.partial_cancelation'
            ],
            'subject' => 'Executive ' . $mail_title . ' [' . $this->reservation->file_code . '] - ' . $reservationsHotels[0]->hotel_code . '-' . $this->reservation->customer_name
        ];

        //        if ($this->reservation->reservator_type == 'client') {

        $clientSellers = User::where('id', $this->reservation->create_user_id)->get(['email']);

        // Email para Clientes
        /*$mailsData[] = [
            'to' => $clientSellers->pluck('email'),
            'cc' => $clientExecutives,
            'mail_class' => PartialCancelationMail::class,
            'mail_data' => [
                'confirmed' => 3,
                'mail_title' => $mail_title,
                'mail_type' => 'ANULACION PARCIAL',
                'mail_config_to' => 'client',
                'comment' => $this->comment,
                'file_code' => $this->reservation->file_code,
                'customer_name' => $this->reservation->customer_name,
                'customer_country' => $client_country_name,
                'executive_code' => isset($executive->code) ? $executive->code : '',
                'executive_name' => $executive->name,
                'executive_email' => $executive->email,
                'created_at' => $this->reservation->created_at,
                'hotels' => $this->reservation->reservationsHotel->toArray(),
                'hotel_code' =>  $reservationsHotels[0]->hotel_code,
                'services' => [],
                'lang' => 'en',
                'template' => 'emails.reservations.partial_cancelation'
            ],
            'subject' => 'Client '.$mail_title . ' ['.$this->reservation->file_code.'] - '.$reservationsHotels[0]->hotel_code . '-' . $this->reservation->customer_name
        ];*/

        //        }

        // Si el nodo no existe o si desde el front envía = 0
        if (isset($this->block_email_provider) && $this->block_email_provider == 1) {
            // No envía comunicación al hotel

            $email_hotels = [
                'to' => [$executive->email],
                'cc' => []
            ];
            //Todo Guardamos lo losg de los emails de los hoteles
            $this->saveEmailsLogs($this->reservation->id, $email_hotels, ReservationsEmailsLog::EMAIL_TO_EXECUTIVE, ReservationsEmailsLog::EMAIL_TYPE_CANCELLATION_PARTIAL);
        } else {
            // Email para Hoteles
            foreach ($this->reservation->reservationsHotel->groupBy('hotel_id') as $hotel_id => $reservationsHotels) {
                $contactHotel = Contact::select('email')
                    ->whereHotelId($hotel_id)->whereNotNull('email')->distinct()
                    ->pluck('email')->toArray();

                if (count($contactHotel) == 0) {
                    throw new Exception('Selected Hotel ' . $hotel_id . ' has not notification emails defined');
                }
                $emailContact_emails = $contactHotel;
                $emailContact = array_shift($contactHotel);
                $contactHotel = array_merge($contactHotel, $emailEjecutivo);


                //            foreach ($reservationsHotels as $reservationsHotel) {
                $mailsData[] = [
                    'to' => $emailContact,
                    'cc' => $contactHotel,
                    'mail_class' => PartialCancelationMail::class,
                    'mail_data' => [
                        'confirmed' => 3,
                        'mail_title' => $mail_title,
                        'mail_type' => 'ANULACION PARCIAL',
                        'mail_config_to' => 'hotel',
                        'comment' => $this->comment,
                        'file_code' => $this->reservation->file_code,
                        'customer_name' => $this->reservation->customer_name,
                        'customer_country' => $client_country_name,
                        'executive_code' => isset($executive->code) ? $executive->code : '',
                        'executive_email' => $executive->email,
                        'created_at' => $this->reservation->created_at,
                        'hotels' => $reservationsHotels->toArray(),
                        'hotel_code' =>  $reservationsHotels[0]->hotel_code,
                        'services' => [],
                        'lang' => 'en',
                        'template' => 'emails.reservations.partial_cancelation'
                    ],
                    'subject' => $mail_title . ' [' . $this->reservation->file_code . '] - ' . $reservationsHotels[0]->hotel_code . '-' . $this->reservation->customer_name,
                ];
                //            }

                $email_hotels = [
                    'to' => (count($emailContact_emails) == 0) ? [] : $emailContact_emails,
                    'cc' => (count($contactHotel) == 0) ? [] : $contactHotel
                ];
                //Todo Guardamos lo losg de los emails de los hoteles
                $this->saveEmailsLogs($this->reservation->id, $email_hotels, ReservationsEmailsLog::EMAIL_TO_HOTEL, ReservationsEmailsLog::EMAIL_TYPE_CANCELLATION_PARTIAL);
            }
        }

        return $mailsData;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function cancel()
    {
        $this->get_penalities();

        if ($this->reservation->reservator_type == "client") {
            $clientExecutives = $this->get_client_executives($this->reservation->client_id);
        } else {
            $clientExecutives = [];
        }

        $mailsData = [];
        // Si el nodo no existe o si desde el front envía = 0
        // if (isset($this->block_email_provider) && $this->block_email_provider==1) {
        //     // No envía comunicación al hotel
        // } else {
        //Todo Correos para hoteles, con copia para ejecutivas
        foreach ($this->reservation->reservationsHotel->groupBy('hotel_id') as $hotel_id => $reservationsHotels) {
            $executive = User::find($this->reservation->executive_id);
            $contactHotel = Contact::select('email')
                ->whereHotelId($hotel_id)->whereNotNull('email')
                ->pluck('email')->toArray();

            if (count($contactHotel) == 0) {
                throw new Exception('Selected Hotel ' . $hotel_id . ' has not notification emails defined');
            }


            //Todo Si el hotel se encuentra en RQ no le enviamos al hotel el correo
            $validateHotelRoomRQ = false;
            $reservationHotel = $reservationsHotels->toArray();
            foreach ($reservationHotel as $hotelReservation) {
                foreach ($hotelReservation['reservations_hotel_rooms'] as $resHotRoom) {
                    if ($resHotRoom['onRequest'] == "0") {
                        $validateHotelRoomRQ = true;
                    }
                }
            }

            $emailTitle = "Hotel Cancellation" . ' [' . $this->reservation->file_code . ']';
            if ($this->channel_cancel_by_rooms_hyperguest != "1") {   // esta variable la mandamos cuando cancelamos por habitaicion, Para hyperguest la tenemos que cancelar todos en grupo y usamos la funcion de cacenlar por hotel.
                if ($validateHotelRoomRQ == true) {
                    continue;
                }
            } else {
                if ($validateHotelRoomRQ == true) {

                    $emailTitle = "Desestimación de solicitud de reserva" . ' [' . $this->reservation->file_code . '] - ' . $reservationsHotels[0]->hotel_code . '-' . $this->reservation->customer_name;
                }
            }

            $email_to = '';

            if (isset($this->block_email_provider) && $this->block_email_provider == 1) {
                // No envía comunicación al hotel
                array_push($clientExecutives, $executive->email);
                $emailContact = $clientExecutives;
                $contactHotel = [];
                $emailContact_emails = $clientExecutives;

                $email_to = ReservationsEmailsLog::EMAIL_TO_EXECUTIVE;
            } else {
                $emailContact_emails = $contactHotel;
                $emailContact = array_shift($contactHotel);
                array_push($clientExecutives, $executive->email);
                $contactHotel = array_merge($contactHotel, $clientExecutives);

                $email_to = ReservationsEmailsLog::EMAIL_TO_HOTEL;
            }

            $mailsData[] = [
                'to' => $emailContact,
                'cc' => $contactHotel,
                'mail_class' => ReservationHotelMail::class,
                'mail_data' => [
                    'confirmed' => 2,
                    'mail_title' => "Cancelación de Reserva",
                    'mail_type' => 'cancelation',
                    'mail_config_to' => 'hotel',
                    'file_code' => $this->reservation->file_code,
                    'customer_name' => $this->reservation->customer_name,
                    'executive_name' => $executive->name,
                    'executive_code' => isset($executive->code) ? $executive->code : '',
                    'executive_email' => $executive->email,
                    'comment' => $this->comment,
                    'created_at' => $this->reservation->created_at,
                    'hotels' => $reservationsHotels->toArray(),
                    'hotel_code' =>  $reservationsHotels[0]->hotel_code,
                    'services' => [],
                    'lang' => 'en',
                    'template' => 'emails.reservations.hotel'
                ],
                'subject' => $emailTitle,
            ];

            $email_hotels = [
                'to' => (count($emailContact_emails) == 0) ? [] : array_unique($emailContact_emails),
                'cc' => (count($contactHotel) == 0) ? [] : array_unique($contactHotel)
            ];

            //Todo Guardamos lo losg de los emails de los hoteles
            $this->saveEmailsLogs(
                $this->reservation->id,
                $email_hotels,
                $email_to,
                ReservationsEmailsLog::EMAIL_TYPE_CANCELLATION
            );
        }

        // }

        return $mailsData;
    }

    public function get_penalities()
    {
        foreach ($this->reservation->reservationsHotel as $reservations_hotel) {
            $business_region_id =  $reservations_hotel->business_region_id;

            foreach ($reservations_hotel->reservationsHotelRooms as $reservationsHotelRoom) {
                $igv = [
                    'percent' => 0,
                    'total_amount' => 0,
                ];
                $extra_fees = json_decode($reservationsHotelRoom['taxes_and_services'], true);
                if (isset($extra_fees['apply_fees']) and isset($extra_fees['apply_fees']['t'])) {
                    foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                        if ($extra_fee['name'] == 'IGV') {
                            $igv['percent'] = $extra_fee['value'];
                            $igv['total_amount'] = $extra_fee['total_amount'];
                        }
                    }
                }
                // ***
                /* TODO: ALEX QUISPE */
                if ($business_region_id == 1) {
                    $policies_cancellation = json_decode($reservationsHotelRoom['policies_cancellation'], true);
                    $policy_cancellation = (object) $policies_cancellation[0];
                    $_apply_date = explode('-', $policy_cancellation->apply_date);
                    $apply_date = $_apply_date[2] . '-' . $_apply_date[1] . '-' . $_apply_date[0];
                    if ($apply_date < Carbon::parse($reservationsHotelRoom["updated_at"])->format('Y-m-d') and $reservationsHotelRoom['onRequest'] != 0 and $reservationsHotelRoom['stella_updated_at'] == null) {
                        $reservationsHotelRoom['penality_base'] = $policy_cancellation->penalty_price;
                        $reservationsHotelRoom["penality"] = $igv['total_amount'] + $policy_cancellation->penalty_price;
                    } else {
                        $reservationsHotelRoom['penality_base'] = 0;
                        $reservationsHotelRoom["penality"] = 0;
                    }
                } else {
                    $reservationsHotelRoom['penality_base'] = 0;
                    $reservationsHotelRoom["penality"] = 0;
                }
            }
        }
    }

    public function get_client_executives($client_id)
    {

        $client_executives = ClientExecutive::where('client_id', $client_id)
            ->where('status', 1)
            ->where('use_email_reserve', 1)
            ->with('user')
            ->get();

        $client_executives_emails = [];

        foreach ($client_executives as $client_executive) {
            if ($client_executive->user && $client_executive->user->user_type_id == 3) {
                array_push($client_executives_emails, $client_executive->user->email);
            }
        }

        return $client_executives_emails;
    }

    public function get_client_kams_executives($client_code)
    {
        try {
            $client_kams_executives = [];
            $stellaService = new StellaService();
            $kam_emails = json_decode(json_encode($stellaService->getKamsByClient($client_code)), true);
            if ($kam_emails['success'] and count($kam_emails['data']) > 0) {
                foreach ($kam_emails['data'] as $email) {
                    $client_kams_executives[] = $email['email'];
                }
            }
        } catch (Exception $exception) {
            $client_kams_executives = [];
        }

        return $client_kams_executives;
    }

    public function get_kam_executive($executive_code)
    {
        try {
            $kams = [];
            $stellaService = new StellaService();
            $kam_emails = json_decode(json_encode($stellaService->getKamByExecutive(strtoupper($executive_code))), true);
            if ($kam_emails['success'] and count($kam_emails['data']) > 0) {
                foreach ($kam_emails['data'] as $email) {
                    $kams[] = $email['email'];
                }
            }
        } catch (Exception $exception) {
            $kams = [];
        }

        return $kams;
    }

    public function saveEmailsLogs($reservation_id, $emails, $email_to, $email_type = 'confirmation')
    {
        $email_log = new ReservationsEmailsLog();
        $email_log->reservation_id = $reservation_id;
        $email_log->email_type = $email_type;
        $email_log->email_to = $email_to;
        $email_log->emails = json_encode($emails);
        $email_log->save();
    }
}
