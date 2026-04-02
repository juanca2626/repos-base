<?php

namespace App\Http\Services\Controllers;

use App\Client;
use App\Http\Controllers\Controller;
use App\Reservation;
use App\ReservationsHotel;
use App\Http\Traits\CalculateCancellationlPolicies;
use App\Http\Traits\CalculateHotelTxesAndServices;
use App\Http\Traits\Reservations;
use App\Quote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

/**
 * Class HotelsReservationsController
 * @package App\Http\Services\Controllers
 */
class HotelsReservationsController extends Controller
{
    use Reservations;
    use CalculateHotelTxesAndServices;
    use CalculateCancellationlPolicies;

    public function __construct()
    {
        $this->reservation_errors = collect();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function showSelection(Request $request)
    {
        try {
            $response = $this->reservationRecheck($request);
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
        }
        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
         try {
            $response = $this->reservationPush($request);
         } catch (\Exception $e) {
             // Verificar si el mensaje es un string JSON
             $message = $e->getMessage();
             if ($this->isJson($message)) {
                 $message = json_decode($message, true); // Convertir el string JSON a un arreglo
             }
             $response = [
                 'success' => false,
                 'error' => [
                     'error_code' => $e->getCode(),
                     'message' => $message
                 ]
             ];

             return Response::json($response);
         }
        return Response::json($response);
    }

    // Método auxiliar para verificar si una cadena es un JSON válido
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }


    /**
     * @param Request $request
     * @param $nro_file
     * @return JsonResponse
     */
    public function show(Request $request, $nro_file)
    {
        try {

            // $reservations_date_update =
            // $data = Reservation::getReservations(['file_code' => $nro_file], true);

            $reser = Reservation::where('file_code', $nro_file)->orWhere('booking_code', $nro_file)->first();

            // die('..'.$reser->max_consecutive_reservations);
            $data = Reservation::getReservations(
                [
                    'file_code' => $nro_file,
                    'hotel_consecutive_from' => $reser->max_consecutive_reservations,
                    // 'service_consecutive_from' => $this->reservation->getConsecutiveServicePrev()
                ],
                true
            );

            $total_reserva = 0;

            $data = $this->reservationToApiResponse($data);
            foreach ($data['reservations_hotel'] as $reservations_hotels) {
                $total_reserva = $total_reserva + $reservations_hotels['total_amount'];
            }

            $repsonse = ['success' => true, 'data' => $data, 'total_reserva' => $total_reserva];
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }

    public function show_from_quote(Request $request)
    {
        try {

            $quote_ids = $request->__get('quotes') ?? [];

            $quotes = Quote::with(['user' => function ($query) {
                $query->withTrashed();
            }, 'reservation'])->whereIn('id', $quote_ids)->get();

            $items = []; $lang = 'en';

            foreach($quotes as $key => $quote)
            {
                $data = []; $reservation = $quote->reservation;

                $data['number'] = $quote->id;
                $data['name'] = $quote->name;
                $data['date'] = date("Y-m-d H:i:s", strtotime($quote->created_at));
                $data['departure'] = $cities[0] ?? 'LIM';
                $data['executive'] = $quote->user->code;
                $data['prices'] = [
                    "estimated" => $quote->estimated_price,
                    "final" => @$reservation->total_amount,
                    "moneda" => "USD",
                ];
                $data['travel_date'] = [
                    "date" => (!empty(@$reservation->date_init)) ? date("Y-m-d H:i:s", strtotime(@$reservation->date_init)) : null,
                    "date_tca" => date("Y-m-d H:i:s", strtotime($quote->estimated_travel_date)),
                ];

                $current_url = explode("/api", $request->url());
                $baseUrlExtra = $current_url[0];
                $data['url'] = $baseUrlExtra;

                if(empty($quote->estimated_price) || $quote->estimated_price == 0)
                {
                    $mount_total = NULL;

                    if($quote->operation == 'passengers')
                    {
                        $quote_people = DB::table('quote_people')
                            ->where('quote_id', '=', $quote->id)
                            ->first();

                        $client = new \GuzzleHttp\Client();
                        $request = $client->get($baseUrlExtra . '/quote_passengers_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                        $response = (array) json_decode($request->getBody()->getContents(), true);
                        $mount_total = NULL; $count = 0; $accommodations = ['', 'SGL', 'DBL', 'TPL'];

                        if(isset($response['data'][0]['passengers']))
                        {
                            foreach($response['data'][0]['passengers'] as $k => $v)
                            {
                                $_name = explode("-", $v['first_name']);
                                $_key = array_search(trim(last($_name)), $accommodations);

                                if($_key > 0)
                                {
                                    $count += $_key;
                                }
                                else
                                {
                                    $count += 1;
                                }

                                if($mount_total == NULL)
                                {
                                    $mount_total = (float) $v['total'];
                                }
                                else
                                {
                                    $mount_total += (float) $v['total'];
                                }
                            }
                        }

                        if(((float) $quote_people->adults + (float) $quote_people->child) > $count)
                        {
                            if($mount_total != NULL AND $mount_total > 0)
                            {
                                $mount_total = (float) ($mount_total * $quote_people->adults);
                            }
                        }
                    }

                    if($quote->operation == 'ranges')
                    {
                        $client = new \GuzzleHttp\Client();
                        $request = $client->get($baseUrlExtra . '/quote_ranges_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                        $response = (array) json_decode($request->getBody()->getContents(), true);

                        if(isset($response['ranges']))
                        {
                            foreach($response['ranges'] as $k => $v)
                            {
                                if($mount_total == NULL)
                                {
                                    $mount_total = (float) $v['promedio'];
                                }
                                else
                                {
                                    if($v['promedio'] <= $mount_total AND $v['promedio'] > 0)
                                    {
                                        $mount_total = (float) $v['promedio'];
                                    }
                                }
                            }
                        }
                    }

                    $mount_total = (float) number_format($mount_total, 4, ".", "");

                    $data['prices']['estimated'] = $mount_total;
                    $quote->estimated_price = $mount_total;
                    $quote->save();
                }

                if($reservation)
                {
                    $data['file'] = [
                        "number" => @$reservation->file_code,
                        "date" => (!empty(@$reservation->created_at)) ? date("Y-m-d H:i:s", strtotime($reservation->created_at)) : null,
                    ];
                }

                $items[] = $data;
            }

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        // try {
            $params = [];
            if ($request->input('file_code')) {
                $params['file_code'] = $request->input('file_code');
            }
            if ($request->input('selected_client')) {
                $params['selected_client'] = $request->input('selected_client');
            }
            if ($request->input('client_id')) {
                $params['client_id'] = $request->input('client_id');
            }
            if ($request->input('option')) {
                $params['option'] = $request->input('option');
            }
            if ($request->input('from_date')) {
                $params['from_date'] = Carbon::parse($request->input('from_date'))->format('Y-m-d');
            }
            if ($request->input('to_date')) {
                $params['to_date'] = Carbon::parse($request->input('to_date'))->format('Y-m-d');
            }
            if ($request->input('reservation_Hotel_code')) {
                $params['reservation_hotel_id'] = $request->input('reservation_Hotel_code');
            }

            $params['from_aurora'] = $request->has('from_aurora') ? $request->input('from_aurora') : false;
            $params['date_order'] = $request->has('date_order') ? $request->input('date_order') : 'asc';

            if (empty($request->input('user_type_id'))) {
                $params['user_type_id'] = 4;
            } else {
                $params['user_type_id'] = $request->input('user_type_id');
            }

            $params['page'] = (!empty($request->input('page'))) ? $request->input('page') : 0;

            $data = ReservationsHotel::getAll($params);
            $reservations = $data['reservations']; $pages = $data['pages']; $total = $data['total'];

            if ($reservations->count() > 0) {

                foreach ($reservations as $reservation) {
                    //solo aremos este auto confirmado si es que el proceso aun esta en stela y la configuracion del cliente sea no OnRequest
                    if (intval($reservation->reservation->status_cron_job_reservation_stella) === Reservation::STATUS_CRONJOB_CREATE_FILE) {
                        $clientConfiguration = isset($reservation->reservation->client->configuration) ? $reservation->reservation->client->configuration : null;
                        if ($clientConfiguration !== null) {
                            if ($clientConfiguration->hotel_allowed_on_request === 0) {
                                $reservation->status = 1;
                            }
                        }
                    }
                }

                $repsonse = ['success' => true, 'data' => $reservations, 'pages' => $pages, 'total' => $total];
            } else {
                $repsonse = ['success' => true, 'data' => []];
            }

        /*
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }
        */

        return Response::json($repsonse);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancellation(Request $request)
    {

        try {

            $filters = [
                'file_code' => $request->input('file_code'),
                'reservation_hotel_id' => $request->input('reservation_Hotel_code'),
                'message_provider' => $request->input('message_provider'),
                'block_email_provider' => $request->input('block_email_provider'),
                'cancel_hotel_or_room' => 1,  // 1=por hotel, 2=por room
                'channel_cancel' => '',
                'channel_cancel_by_rooms_hyperguest' => ''
            ];

            $reservation = Reservation::getReservations($filters, true);

            if (!$reservation) {
                throw new \Exception('File number ' . $request->input('file_code') . ' does not exist');
            }

            if ($reservation->status_cron_job_reservation_stella != Reservation::STATUS_CRONJOB_CLOSE_PROCESS) {
                throw new \Exception('The reservation is being processed, please try again in 1 min');
            }

            if (count($reservation->reservationsHotel) == 0) {
                throw new \Exception('The hotel reservation code (reservation_Hotel_code) does not exist');
            }

            foreach ($reservation->reservationsHotel as $reservationsHotel) {
                foreach ($reservationsHotel->reservationsHotelRooms as $reservations_hotel_rooms) {
                    if ($reservations_hotel_rooms->onRequest == "0") {
                        throw new \Exception('It is not possible to cancel if there is a record in RQ in the reservation, the cancellation must be made per room');
                    }
                }
            }


            $reservation = $this->cancelReservationHotel($filters);

            $response = ['success' => true, 'data' => $reservation];

            return Response::json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'message' => $e->getMessage(),
                'error' => 'Unexpected error while cancelling the reservation.',
            ], 500);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add_hotel_room(Request $request)
    {
        try {
            $repsonse = $this->reservationAddHotelRoom($request);
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function change_hotel_dates(Request $request)
    {
        try {
            $repsonse = $this->reservationChangeDatesHotel($request);
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel_hotel_room(Request $request)
    {
        try {
            $block_email_provider = $request->has('block_email_provider') ? $request->input('block_email_provider') : 0;
            if ($request->input('channel') == "6") {
                $reservation = $this->cancelReservationHotel([
                    'file_code' => $request->input('file_code'),
                    'reservation_hotel_id' => $request->input('reservation_Hotel_code'),
                    'message_provider' => $request->input('message_provider'),
                    'block_email_provider' => $block_email_provider,
                    'cancel_hotel_or_room' => 2,
                    'channel_cancel_by_rooms_hyperguest' => 1
                ]);
                $response = ['success' => true, 'data' => $reservation];
            } else {
                $reservation = $this->cancelReservationHotelRoom([
                    'file_code' => $request->input('file_code'),
                    'reservation_hotel_id' => $request->input('reservation_Hotel_code'),
                    'reservation_hotel_room_id' => $request->input('reservation_Hotel_room_code'),
                    'message_provider' => $request->input('message_provider'),
                    'block_email_provider' => $block_email_provider,
                    'cancel_hotel_or_room' => 2  // 1=por hotel, 2=por room
                ]);

                // VALIDAR QUE LA RESPUESTA NO SEA UN ERROR
                // YA QUE EL SERVICIO DE CANCELACION DE HYPERGUEST DEVUELVE UN 200 AUNQUE HAYA UN ERROR EN LA CANCELACION, POR LO QUE SE DEBE VALIDAR EL CONTENIDO DE LA RESPUESTA PARA SABER SI FUE EXITOSA O NO
                if ($reservation['success']) {
                    $data = $this->reservationToApiResponse($reservation['data']);
                    $response = ['success' => $reservation['success'], 'data' => $data];
                } else {
                    $response = ['success' => false, 'error' => $reservation];
                }

            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_document' => $e->getFile()
            ];
        }

        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function executives(Request $request)
    {
        try {
            if (auth_user()->user_type_id == 4) {// cliente
                // client
                /** @var Client $client */
//                dd(auth_user());
                $clientSeleccionado = auth_user()->clientSellers()
                    ->where('clients.status', 1)
                    ->wherePivot('status', 1)
                    ->first();

                if (!$clientSeleccionado) {
                    throw new \Exception('Unable To process | error: 3315');
                }

                if ($clientSeleccionado->status != 1) {
                    throw new \Exception('Unable To process | error: 3316');
                }
            } else {
                throw new \Exception('Unable To process | error: 3317');
            }

            $cliente = $clientSeleccionado->users()
                ->where('users.user_type_id', 3)
                ->where('users.status', 1)->get();

            $result = [];

            if (count($cliente) > 0) {
                foreach ($cliente as $executive) {

                    array_push($result, [
                        'executive_id' => $executive->id,
                        'name' => $executive->name
                    ]);

                }
            }


            $repsonse = ['success' => true, 'data' => $result];
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }

    public function storeNroConfirmation(Request $request)
    {
        try {
            $response_ws = $this->addNumberConfirmationRoomHotel([
                'file_code' => $request->input('file_code'),
                'reservation_hotel_id' => $request->input('reservation_hotel_id'),
                'room_codes' => $request->input('room_codes'),
                'nro_confirmation' => $request->input('nroconfirmation'),
            ]);

            $response = ['success' => $response_ws];
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
        }
        return Response::json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancellation_service(Request $request)
    {
        // die('..');
        try {
            $reservation = $this->cancelReservationService([
                'file_code' => $request->input('file_code'),
                'reservation_service_id' => $request->input('reservation_service_code'),
            ]);
            // die('proceso final');
            $repsonse = ['success' => true, 'data' => $reservation];
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }

    public function relateOrder(Request $request)
    {
        $booking_code = $request->get('booking_code');
        $nroped = $request->get('nroped');
        $nroord = $request->get('nroord');

        try {
            $reservation = Reservation::where('booking_code', $booking_code)->first();
            if ($reservation) {
                $reservation->status_cron_jon_order_stella = 1;
                $reservation->order_number = $nroped;
                $reservation->order_number_sort = $nroord;
                $reservation->save();
                $repsonse = ['success' => true];
            } else {
                $repsonse = ['success' => false, 'error' => 'not found'];
            }
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);

    }

    public function searchTokenService($token_search)
    {
        $serviceSearch = $this->checkTokenSearchService($token_search);
        return [
            'data' => $serviceSearch,
            'count' => isset($serviceSearch['error_code']) ? 0 : count($serviceSearch)
        ];
    }

}
