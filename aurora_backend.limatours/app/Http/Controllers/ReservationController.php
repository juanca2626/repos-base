<?php

namespace App\Http\Controllers;

use App\ChannelsLogs;
use App\Client;
use App\ClientSeller;
use App\File;
use App\Http\Resources\Reservation\ReservationDetailsResource;
use App\MasiFileDetail;
use App\Reservation;
use App\ReservationsHotel;
use App\ReservationsHotelsRatesPlansRooms;
use App\ReservationsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

/**
 * Class ReservationController
 * @package App\Http\Controllers
 */
class ReservationController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:bookings.read')->only('index');
        $this->middleware('permission:bookings.create')->only('store');
        $this->middleware('permission:bookings.update')->only('update');
        $this->middleware('permission:bookings.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 50;

        try {
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

            if ($request->input('create_date')) {
                $params['create_date'] = Carbon::parse($request->input('create_date'))->format('Y-m-d');
            }

            if ($request->input('status_reserve')) {
                $params['status_reserve'] = $request->input('status_reserve');
            }

            if ($request->input('status_error')) {
                $params['status_error'] = $request->input('status_error');
            }

            if ($request->input('user_type_id')) {
                $params['user_type_id'] = $request->input('user_type_id');
            }

            $params['page'] = $paging;
            $params['limit'] = $limit;
            $reservations = Reservation::getAllReservationPaginate($params);

            $reservations['data']->transform(function ($item) {
                $item->reservationsService->transform(function ($service) {
                    $service->resend_email_enable = false;
                    return $service;
                });
                $item->reservationsHotel->transform(function ($hotel) {
                    $hotel->resend_email_enable = false;
                    $hotel->reservationsHotelRooms->transform(function ($hotelRoom) {

                        $hotelRoom->reservationsHotelsCalendarys->transform(function ($hotelRoomCalendar) {
                            $hotelRoomCalendar->policies_rates = json_decode($hotelRoomCalendar->policies_rates, true);
                            return $hotelRoomCalendar;
                        });

                        $hotelRoom->cancel_details = '';
                        if ($hotelRoom->cancel_updated_at) {
                            $hotelRoom->cancel_details .= "Usuario: " . $hotelRoom->user_cancel->code . "<br/>";
                            $hotelRoom->cancel_details .= "Método: " . ($hotelRoom->cancel_hotel_or_room == "1" ? "Por Hotel" : "Por Habitación") . "<br/>";
                            $hotelRoom->cancel_details .= "Fecha: " . $hotelRoom->cancel_updated_at . "<br/>";
                            $hotelRoom->cancel_details .= "Se notifico al hotel: " . ($hotelRoom->cancel_block_email_provider == "1" ? "No" : "Si") . "<br/>";
                        }
                        return $hotelRoom;
                    });
                    return $hotel;
                });
                return $item;
            });

            $repsonse = ['success' => true, 'data' => $reservations['data'], 'count' => $reservations['count']];
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogs($id)
    {
        $logs = ChannelsLogs::where('reservation_id', $id)
            ->where(function ($query) {
                $query->where('method_name', 'CreandoFile');
                $query->orWhere('method_name', 'CancelaFile');
                $query->orWhere('method_name', 'CreandoCliente');
                $query->orWhere('method_name', 'RequestCreateFile');
            })
            ->limit(50)
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'reservation_id',
                'method_name',
                'log_directory',
                'type_data',
                'log_request',
                'log_response',
                'created_at'
            ]);
        $logs->each(function ($item, $key) {
            if (!empty($item->log_request) and !empty($item->log_response) and $item->type_data === 'json') {
                $item->log_response = json_decode($item->log_response);
                $item->log_request = json_decode($item->log_request);
            }
        });
        return Response::json($logs);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showItinerary(Request $request, $id)
    {
        try {
            $ids = explode(",", $id);

            $reservations = Reservation::with(['reservationsHotel' => function ($query) {
                $query->with(['reservationsHotelRooms']);
                // $query->select(['reservation_id', 'hotel_code', 'hotel_name', 'check_in', 'check_in_time', 'total_amount']);
            }, 'reservationsService' => function ($query) {
                $query->select(['id', 'reservation_id', 'service_id', 'service_code', 'service_name', 'adult_num', 'child_num', 'date', 'time', 'total_amount']);
            }, 'reservationsFlight' => function ($query) {
                $query->select(['id', 'reservation_id', 'code_flight', 'adult_num', 'child_num', 'origin', 'destiny', 'date']);
            }])
                ->whereIn('id', $ids)->select(['id', 'object_id', 'entity'])->get();

            $response = [];

            $reservations->each(function ($reservation) use (&$response) {
                $all_services = collect();

                $all_services = $all_services->mergeRecursive($reservation['reservationsFlight']);
                $all_services = $all_services->mergeRecursive($reservation['reservationsHotel']);
                $all_services = $all_services->mergeRecursive($reservation['reservationsService']);

                $new_services = collect();

                $all_services->each(function ($service) use (&$new_services) {

                    if (isset($service['hotel_code'])) {
                        $adult_num = 0;
                        $child_num = 0;

                        $service['reservationsHotelRooms']->each(function ($room) use (&$adult_num, &$child_num) {
                            $adult_num += $room['adult_num'];
                            $child_num += $room['child_num'];
                        });

                        $new_service = [
                            'id' => $service['id'],
                            'type' => 'hotel',
                            'hotel_id' => $service['hotel_id'],
                            'code' => $service['hotel_code'],
                            'name' => $service['hotel_name'],
                            'adult' => $adult_num,
                            'child' => $child_num,
                            'infant' => 0,
                            'nights' => $service['nights'],
                            'total_amount' => $service['total_amount'],
                            'check_in' => $service['check_in'],
                            'check_in_time' => $service['check_in_time'],
                            'check_out' => $service['check_out'],
                            'check_out_time' => $service['check_out_time'],
                        ];
                    }

                    if (isset($service['service_code'])) {
                        $new_service = [
                            'id' => $service['id'],
                            'type' => 'service',
                            'code' => $service['service_code'],
                            'name' => $service['service_name'],
                            'adult' => $service['adult_num'],
                            'child' => $service['child_num'],
                            'infant' => $service['infant_num'],
                            'total_amount' => $service['total_amount'],
                            'check_in' => $service['date'],
                            'check_in_time' => $service['time'],
                            'service_id' => $service['service_id'],
                        ];
                    }

                    if (isset($service['code_flight'])) {
                        $new_service = [
                            'id' => $service['id'],
                            'type' => 'flight',
                            'code' => $service['code_flight'],
                            'adult' => $service['adult_num'],
                            'child' => $service['child_num'],
                            'infant' => $service['inf_num'],
                            'total_amount' => 0,
                            'origin' => $service['origin'],
                            'destiny' => $service['destiny'],
                            'check_in' => $service['date'],
                            'check_in_time' => '00:00:00',
                        ];
                    }

                    $new_service['timestamp'] = strtotime($new_service['check_in'] . ' ' . $new_service['check_in_time']);
                    $new_services->push($new_service);

                    return $service;
                });

                $all_services = $new_services->sortBy('timestamp');

                $response[$reservation->id] = [
                    'all_services' => $all_services,
                    'reservation' => $reservation,
                ];
            });

            if (count($ids) == 1) {
                $id = $request->__get('id');
                $data = [
                    'success' => true,
                    'data' => $response[$id]['all_services']->values()->all(),
                    'count' => $response[$id]['all_services']->count(),
                    'quote' => $response[$id]['reservation']->object_id,
                    'entity' => $response[$id]['reservation']->entity,
                ];
            } else {
                $data = $response;
            }
        } catch (\Exception $e) {
            $data = ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showDetail($file_code)
    {
        try {
            $data = [
                'success' => false,
                'data' => [],
            ];
            $findFile = Reservation::where('file_code', $file_code)
                ->where('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CLOSE_PROCESS)
                ->limit(1)
                ->get(['id', 'file_code', 'customer_name']);
            if ($findFile->count() > 0) {
                $data = [
                    'success' => true,
                    'data' => $findFile,
                ];
            }
        } catch (\Exception $e) {
            $data = ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
        return Response::json($data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::find($id);
            $reservation->status_cron_job_error = Reservation::STATUS_CRONJOB_ERROR_FALSE;
            $reservation->save();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function executiveUpdate(Request $request, $id)
    {
        try {

            $file = base64_decode($id);

            $reservation = Reservation::find($file);

            if ($reservation and $reservation->status_cron_job_error == Reservation::STATUS_CRONJOB_SEND_ERROR_NOTIFICATION) {

                $reservation->status_cron_job_error = Reservation::STATUS_CRONJOB_ERROR_FALSE;
                $reservation->save();

                return view('reservations.alert');
            } else {
                return redirect(config('services.aurora_front.domain'));
            }


        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $delete = Reservation::find($id);
            $delete->delete_user_id = Auth::id();
            $delete->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function relateOrder(Request $request)
    {
        try {
            $nroref = $request->get('nroref');
            $nroped = $request->get('nroped');
            $nroord = $request->get('nroord');
            $reservation = Reservation::where('booking_code', $nroref)->first();
            $reservation->order_number = $nroped;
            $reservation->order_number_sort = $nroord;
            $reservation->status_cron_jon_order_stella = Reservation::STATUS_CRONJOB_CREATE_RELATIONSHIP_ORDER;
            $reservation->save();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function resendEmailReservations($id, Request $request)
    {
        $reservations_services_ids = $request->get('services');
        $reservations_hotels_ids = $request->get('hotels');

        try {
            DB::beginTransaction();
            if (count($reservations_services_ids)) {
                DB::table('reservations_services')->whereIn('id',
                    $reservations_services_ids)->update(['status_email' => ReservationsService::STATUS_EMAIL_NOT_SENT]);
            }
            if (count($reservations_hotels_ids)) {
                DB::table('reservations_hotels')->whereIn('id',
                    $reservations_hotels_ids)->update(['status_email' => ReservationsHotel::STATUS_EMAIL_NOT_SENT]);
            }
            $reservation = Reservation::find($id);
            $reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_RESERVE;
            $reservation->save();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateExecutive($id, Request $request)
    {
        $executive_id = $request->input('executive_id');
        try {
            DB::beginTransaction();
            $executive = User::find($executive_id, ['id', 'code', 'name']);
            $reservation = Reservation::find($id);
            $reservations_hotels = ReservationsHotel::where('reservation_id',
                $reservation->id)
                ->where('status', '!=', 0)
                ->where('executive_id', $reservation->executive_id)
                ->get([
                    'id',
                    'executive_id',
                    'executive_name',
                    'executive_code'
                ]);
            foreach ($reservations_hotels as $hotel) {
                $reservations_hotel = ReservationsHotel::find($hotel->id);
                $reservations_hotel->executive_id = $executive->id;
                $reservations_hotel->executive_name = $executive->name;
                $reservations_hotel->executive_code = $executive->code;
                $reservations_hotel->save();
            }

            $reservations_hotels_rates = ReservationsHotelsRatesPlansRooms::whereIn('reservations_hotel_id',
                $reservations_hotels->pluck('id'))->get([
                'id',
                'executive_id',
                'executive_name'
            ]);
            foreach ($reservations_hotels_rates as $rate) {
                $reservations_hotel_rate = ReservationsHotelsRatesPlansRooms::find($rate->id);
                $reservations_hotel_rate->executive_id = $executive->id;
                $reservations_hotel_rate->executive_name = $executive->name;
                $reservations_hotel_rate->save();
            }

            $reservations_services = ReservationsService::where('reservation_id',
                $reservation->id)->where('executive_id', $reservation->executive_id)->get([
                'id',
                'executive_id',
                'executive_name',
                'executive_code'
            ]);

            foreach ($reservations_services as $service) {

                $reservations_service = ReservationsService::find($service->id);
                $reservations_service->executive_id = $executive->id;
                $reservations_service->executive_name = $executive->name;
                $reservations_service->executive_code = $executive->code;
                $reservations_service->save();
            }

            $reservation->executive_id = $executive->id;
            $reservation->create_user_id = $executive->id;
            $reservation->executive_name = $executive->name;
            $reservation->save();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateClient($id, Request $request)
    {
        $executive_id = $request->input('client_id');
        try {
            DB::beginTransaction();
            $client = Client::find($executive_id, ['id', 'code']);
            $reservation = Reservation::find($id);
            $reservation->client_id = $client->id;
            $reservation->client_code = $client->code;
            $reservation->save();

            $file = File::where('file_number', '=', $reservation->file_code)->first();
            if ($file) {
                $file->client_id = $client->id;
                $file->save();
            }

            $masi = MasiFileDetail::where('file', '=', $reservation->file_code)->first();
            if ($masi) {
                $masi->client = $client->code;
                $masi->save();
            }

            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource for clients.
     *
     * @return \Illuminate\Http\Response
     */
    public function getReservationByCode(Request $request, $reservation_id = null)
    {
        try {
            $params = [];

            $params['file_code'] = $request->input('file_code');

            if ($params['file_code'] == "") {
                throw new \Exception("Enter a file number");
            }

            $client_data = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            if ($client_data) {
                $params['client_id'] = $client_data["client_id"];
            } else {
                throw new \Exception("does not have an associated client");
            }
            $reservations = Reservation::getReservationByCode($params);

            $reservations->transform(function ($item) {
                $item->reservationsService->transform(function ($service) {
                    $service->resend_email_enable = false;
                    return $service;
                });
                $item->reservationsHotel->transform(function ($hotel) {
                    $hotel->resend_email_enable = false;
                    $hotel->reservationsHotelRooms->transform(function ($hotelRoom) {

                        $hotelRoom->reservationsHotelsCalendarys->transform(function ($hotelRoomCalendar) {
                            $hotelRoomCalendar->policies_rates = json_decode($hotelRoomCalendar->policies_rates, true);
                            return $hotelRoomCalendar;
                        });

                        $hotelRoom->cancel_details = '';
                        if ($hotelRoom->cancel_updated_at) {
                            $hotelRoom->cancel_details .= "Usuario: " . $hotelRoom->user_cancel->code . "<br/>";
                            $hotelRoom->cancel_details .= "Método: " . ($hotelRoom->cancel_hotel_or_room == "1" ? "Por Hotel" : "Por Habitación") . "<br/>";
                            $hotelRoom->cancel_details .= "Fecha: " . $hotelRoom->cancel_updated_at . "<br/>";
                            $hotelRoom->cancel_details .= "Se notifico al hotel: " . ($hotelRoom->cancel_block_email_provider == "1" ? "No" : "Si") . "<br/>";
                        }
                        return $hotelRoom;
                    });
                    return $hotel;
                });
                return $item;
            });


            $reservations = ReservationDetailsResource::collection($reservations);
            return Response::json(['success' => true, 'data' => $reservations]);

        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);

    }

    public function getFileNumber($fileNumber)
    {
        $reservation = Reservation::with(['client', 'executive', 'reservationsHotel', 'reservationsService'])
            ->where('entity','<>', 'Stella')
            ->where('file_code', $fileNumber)
            ->first();

        if (!$reservation) {
            return Response::json([
                'error' => 'File not found',
                'fileNumber' => $fileNumber
            ], 404);
        }

        $datesCheckIn = collect();
        $datesCheckOut = collect();

        if ($reservation->reservationsHotel->count() > 0) {
            foreach ($reservation->reservationsHotel as $reservationHotel) {
                $datesCheckIn->add($reservationHotel['check_in']);
                $datesCheckOut->add($reservationHotel['check_out']);
            }
        }

        if ($reservation->reservationsService->count() > 0) {
            foreach ($reservation->reservationsService as $reservationService) {
                $datesCheckIn->add($reservationService['date']);
                $datesCheckOut->add($reservationService['date']);
            }
        }

        $data = [
            'fileNumber' => $reservation->file_code,
            'groupName' => $reservation->customer_name,
            'datein' => $datesCheckIn->min() ?? $reservation->date_init,
            'clientName' => $reservation->client ? $reservation->client->code : null,
            'advisorEmail' => $reservation->executive ? $reservation->executive->email : null,
            'advisorName' => $reservation->executive ? $reservation->executive->name : null,
            'codven' => $reservation->executive ? $reservation->executive->code : null,
            'dateout' => $datesCheckOut->max(),
            'groupType' => null,
        ];

        return Response::json($data);
    }
}
