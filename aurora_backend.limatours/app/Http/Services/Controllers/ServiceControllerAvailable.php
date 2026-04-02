<?php

namespace App\Http\Services\Controllers;

use App\City;
use App\Room;
use App\Zone;
use App\State;
use Exception;
use App\Markup;
use App\Language;
use Carbon\Carbon;
use App\RatesPlans;
use App\ChannelHotel;
use App\BusinessRegion;
use App\ClientRatePlan;
use App\DateRangeHotel;
use App\RatesPlansRooms;
use App\Http\Traits\Hotels;
use App\Http\Traits\Images;
use Faker\Factory as Faker;
use App\Http\Traits\Package;
use App\Http\Traits\Services;
use App\RatesPlansPromotions;
use App\Http\Traits\Currencies;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Traits\ManageSearchHotel;
use App\Http\Traits\ValidateHotelSearch;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\HotelSearchRequest;
use App\Http\Services\Traits\ClientTrait;
use App\Http\Traits\HotelsAvailByDestination;
use App\Http\Traits\AddHotelRateTaxesAndServices;
use App\Http\Services\Traits\ClientHotelUtilTrait;
use App\Http\Traits\CalculateHotelTxesAndServices;
use App\Http\Traits\CalculateCancellationlPolicies;
use App\Http\Services\Traits\ServiceControllerTrait;
use App\Http\Services\Traits\ClientHotelAvailableTrait;
use App\Http\Services\Traits\ClientHotelOnRequestTrait;
use App\Http\Multichannel\Hyperguest\Services\AvailabilityHyperguestGatewayService;

class ServiceControllerAvailable extends Controller
{
    use ManageSearchHotel,
        ValidateHotelSearch,
        CalculateCancellationlPolicies,
        CalculateHotelTxesAndServices,
        HotelsAvailByDestination,
        ClientTrait,
        Services,
        Package,
        Images,
        Currencies,
        Hotels,
        AddHotelRateTaxesAndServices,
        ServiceControllerTrait,
        ClientHotelUtilTrait,
        ClientHotelAvailableTrait,
        ClientHotelOnRequestTrait;

    /**
     * @var int time before expire cache search expressed in minutes
     */
    public $expiration_search_hotels = 180; // 3 horas
    public $expiration_search_services = 1440; // 24 horas


    public function hotels_channels(HotelSearchRequest $request)
    {
        try
        {
            $destiny = $request->get('destiny');

            $locationCodes = $this->extractLocationCodes($destiny);

            $country_id = $locationCodes['country_id'];
            $state_id = $locationCodes['state_id'];
            $city_id = $locationCodes['city_id'];
            $district_id = $locationCodes['district_id'];
                   
            if (!$country_id) {
                $country_id = 'PE'; // por defecto peru
            }            

            // TODO: Obtenemos el Id del cliente
            $client_id = $this->getClientId($request);

            // TODO: Obtenemos el la región del cliente en base al pais
            $client_business = $this->getClientBusinessRegion($client_id, $country_id);
 
            if(!$client_business)
            {
                $bussinnes_regions = $this->getBusinessRegion($country_id);

                // $country = Country::find($country_id);
                if($bussinnes_regions)
                    throw new Exception(trans('The client does not have the '.$bussinnes_regions->business_region->description.' associated with them'));
                else
                    throw new Exception(trans('You have not selected a region'));
            }

            $response1 = $this->hotels($request, $client_id, $country_id, $state_id, $city_id, $district_id);

            $availabilityHGService = new AvailabilityHyperguestGatewayService();
            $response2 = $availabilityHGService->hotels($request, $client_id);

            $response = $this->mergeHotelResponses($response1, $response2, $this->expiration_search_hotels);

            if ($response['success']) {
                // return Response::json($response);

                $responseJson = response()->json($response, 200);

                $responseJson->header('Connection', 'close');
                $responseJson->header('Content-Length', strlen($responseJson->getContent()));

                return $responseJson;
                //    $json = json_encode($response, JSON_UNESCAPED_UNICODE);

                //     return response($json, 200)
                //         ->withHeaders([
                //             'Content-Type' => 'application/json',
                //             'Content-Length' => strlen($json),
                //             'Connection' => 'close'
                //         ]);
            } else {
                $status_code = $response['status_code'] > 0 ? $response['status_code'] : 500;
                return Response::json([
                    'success' => $response['success'],
                    'error' => $response['error'],
                ], $status_code);
            }

        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }

    }

    public function hotels(HotelSearchRequest $request, $client_id, $country_id, $state_id, $city_id, $district_id): array
    {

        $priceRange = $request->get('price_range');

        $hotels_id = $request->get('hotels_id');
        if (!isset($hotels_id)) {
            $hotels_id = [];
        }

        $userTypeId = Auth::user()->user_type_id;

        $rate_plan_room_search = $request->get('rate_plan_room_search', []);
        $allow_children = $request->get('allow_children', false);
        $hotels_search_code = $request->get('hotels_search_code');
        $hotels_search_string = '';
        if (isset($hotels_search_code)) {
            if (!is_array($hotels_search_code)) {
                // Si es string, conviértelo a array
                $hotels_search_code = explode(',', $hotels_search_code);
                // Y genera el string (por si lo necesitas)
            }

            $hotels_search_string = implode(', ', $hotels_search_code);

            // Asegurarse de que cada código esté limpio (sin espacios extra)
            $hotels_search_code = array_map('trim', $hotels_search_code);
            // Ahora realiza la consulta usando el array
            $hotels_from_search_code = ChannelHotel::where('channel_id', 1)
                ->whereIn('code', $hotels_search_code)
                ->pluck('hotel_id')
                ->toArray();

            // Fusiona con $hotels_id (asegúrate de que $hotels_id sea un array)
            $hotels_id = array_unique(array_merge((array)$hotels_id, $hotels_from_search_code));

        }


        if (!$request->has('destiny')) {
            $request->merge([
                'destiny' => [
                    'code' => '',
                    'label' => ''
                ]
            ]);
        }

        $destiny = $request->get('destiny');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $typeclass = $request->get('typeclass_id');
        $setMarkup = $request->get('set_markup');
        $zeroRates = $request->get('zero_rates', false);
        $preferential = $request->get('preferential', false);

        if (!isset($setMarkup)) {
            $setMarkup = 0;
        }
        $quantity_persons_rooms = $request->get('quantity_persons_rooms', []);
        $promotional_rate = (int)@$request->get('promotional_rate');
        if ($request->has('lang') and !empty($request->get('lang'))) {
            $lang_iso = $request->get('lang');
        } else {
            $lang_iso = 'en';
        }
        $language = Language::where('iso', $lang_iso)->where('state', 1)->first();
        if (!$language) {
            $language = Language::where('iso', "en")->where('state', 1)->first();
            $lang_iso = 'en';
        }
        App::setLocale($lang_iso);
        $language_id = $language->id;
        $search_parameters = [
            'client_id' => $client_id,
            'destiny' => $destiny,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'typeclass_id' => $typeclass,
            'quantity_persons_rooms' => $quantity_persons_rooms,
            'promotional_rate' => $promotional_rate,
        ];


        $typeclass_id = "";

        //Asignar si existe filtro por clase de hotel
        if ($typeclass != "all" && $typeclass != "hotel_id") {
            $typeclass_id = $typeclass;
        }

        $quantityPersons = $this->getQuantityPersonsRoom($quantity_persons_rooms, $allow_children);

        $accept_child = $quantityPersons->get('accept_child');
        $child_min_age = $quantityPersons->get('child_min_age');

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);

        $this->setClient($client_id);
        $period = $from->year;

        if (empty($this->_client)) {
            throw new Exception(trans('validations.quotes.client_not_found'));
        }

        //Todo Variable que nos permite identidicar si el cliente permite ver las tarifas en On Request
        $clientAllowedOnRequest = true;
        if ($this->_client->configuration !== null) {
            $clientAllowedOnRequest = $this->_client->configuration->hotel_allowed_on_request;
        }

        // try {
        if (!$setMarkup) {

            $client_markup = Markup::whereHas('businessRegion.countries', function ($query) use ($country_id) {
                $query->where('iso', $country_id);
            })->where(['client_id' => $client_id, 'period' => $period])->first();


            if (!$client_markup) {
                throw new Exception(trans('validations.quotes.client_does_not_have_markup_for_year', ['year' => $period]));
            }
        }

        if ($accept_child) {
            //Logica para verificar si los hoteles aceptan niños
            $hotels_client_hotel_id_list = $this->getClientHotelsIds($country_id, $state_id, $city_id, $district_id,
                $typeclass_id, $period, $hotels_id, $hotels_search_string, $child_min_age, $preferential);

        } else {

            $hotels_client_hotel_id_list = $this->getClientHotelsIds($country_id, $state_id, $city_id, $district_id,
                $typeclass_id, $period, $hotels_id, $hotels_search_string, false, $preferential);
        }

        $reservation_days = $from->diffInDays($to);
        $reservation_start_date = Carbon::now('America/Lima')->startOfDay();
        $days_advance_reservation = $reservation_start_date->diffInDays($from);

        $from = $from->format('Y-m-d');
        $today = Carbon::now('America/Lima')->startOfDay()->format('Y-m-d');
        //TODO asgregar validacion que el to debe de ser mayor o igual que el from
        $to = $to->subDay(1)->format('Y-m-d');

        $current_date = Carbon::now('America/Lima')->startOfDay();
        $check_in = Carbon::parse($date_from);
        $check_out = Carbon::parse($date_to);

        $hotels_client = [];
        if ($from > $today) {
            $query = ClientRatePlan::query();
            $query->where('client_id', $client_id);
            $query->where('period', $period);
            $rate_plans_ids_ignore = $query->pluck('rate_plan_id')->toArray();

            $query = RatesPlans::query();
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->where('status', 1);

            if ($promotional_rate == 1) {
                $query->where('promotions', 1);
            }
            if (!count($quantity_persons_rooms)) {
                if ($userTypeId !== 3) {
                    $query->where('price_dynamic', 0);
                }
            } else {
                $query->where('price_dynamic', 0);
            }

            $query->whereNotIn('id', $rate_plans_ids_ignore);
            $rate_plans_available = $query->pluck('id')->toArray();

            $query = Room::query();
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->where('state', 0);
            $rooms_ids_ignore = $query->pluck('id')->toArray();

            // ✅ Para AURORA (channel_id = 1)
            $query = RatesPlansRooms::query();
            $query->whereIn('rates_plans_id', $rate_plans_available);
            $query->whereNotIn('room_id', $rooms_ids_ignore);
            $query->where('status', 1);
            $query->where('channel_id', 1); // AURORA
            if (!empty($rate_plan_room_search)) {
                $query->whereIn('id', $rate_plan_room_search);
            }
            $rate_plan_room_ids_AURORA_include = $query->pluck('id');

            // Clonamos para HYPERGUEST
            // ✅ Para HYPERGUEST (channel_id <> 1)
            $query = RatesPlansRooms::query();
            $query->whereIn('rates_plans_id', $rate_plans_available);
            $query->whereNotIn('room_id', $rooms_ids_ignore);
            $query->where('status', 1);
            $query->where('channel_id', '<>', 1); // HYPERGUEST
            if (!empty($rate_plan_room_search)) {
                $query->whereIn('id', $rate_plan_room_search);
            }
            $rate_plan_room_ids_HYPERGUEST_include = $query->pluck('id');

            //TODO agregar a la logica que si trae niños al menos igual a 0
            //validacion de tarifas Aurora para ver si tienen una rate_plan_calendary y un importe de adulto o importe total > 0 para el rango de fechas de reservacion
            $rate_plan_room_ids_include = $this->getArrayIdsRatesPlansRoomsCalendarsInRangeReservationDaysAndAmountAdultOrAmountTotal(
                $hotels_client_hotel_id_list,
                $from,
                $to,
                $reservation_days,
                $date_to,
                $rate_plan_room_ids_AURORA_include,
                $rate_plan_room_ids_HYPERGUEST_include
            );

            //Esto es para tarifas de Channels validando tiempo de estadia y días de anticipacion de reserva
            $rate_plan_room_channels_time_stay_and_days_advance_reservation = $this->getArrayIdsRatesPlansRoomsChannelsTimeStayAndDaysAdvanceReservation(
                $hotels_client_hotel_id_list,
                $from,
                $days_advance_reservation,
                $reservation_days,
                $rate_plan_room_search
            );

            if (count($rate_plan_room_channels_time_stay_and_days_advance_reservation) > 0) {
                // Validacion de tarifas de channel para ver si tiene un importe para el numero de adultos de busqueda o tiene un importe total por habitacion
                $rate_plan_room_channels_validate_calendar = $this->getArrayIdsRatesPlansRoomsChannelsCalendar(
                    $hotels_client_hotel_id_list,
                    $from,
                    $to,
                    $rate_plan_room_channels_time_stay_and_days_advance_reservation,
                    $reservation_days,
                    $quantity_persons_rooms
                );
                if (count($rate_plan_room_channels_validate_calendar) > 0) {
                    foreach ($rate_plan_room_channels_validate_calendar as $rate_channel) {
                        $rate_plan_room_ids_include[] = $rate_channel;
                    }
                }
            }

            //Obtener Tarifas OnRequest
            $rates_plan_rooms_on_request = $rate_plan_room_ids_include;

            //validacion de tarifas para ver si tienen inventario de al menos 1 para el rango de fechas de reservacion que no estan en una bolsa
            $rate_plan_room_ids_include = $this->getArrayIdsRatesPlansRoomsHaveOneInventory(
                $rate_plan_room_ids_include,
                $hotels_client_hotel_id_list,
                $from,
                $to,
                $reservation_days
            );

            $rates_plan_rooms_on_request = array_diff($rates_plan_rooms_on_request, $rate_plan_room_ids_include);

            $hotels_client = $this->getClientHotelsAvail(
                $client_id,
                $period,
                $hotels_client_hotel_id_list,
                $date_from,
                $date_to,
                $from,
                $to,
                $reservation_days,
                $rate_plan_room_ids_include,
                $country_id,
                $state_id,
                $city_id,
                $district_id,
                $typeclass_id,
                $hotels_id,
                $language->id
            );
        }

        //Mejores Opciones por hotel
        foreach ($hotels_client as $index_hotel => $hotel_client) {
            $hotels_client[$index_hotel]['best_options'] = [
                "quantity_rooms" => 0,
                "quantity_adults" => 0,
                "quantity_child" => 0,
                "total_taxes_and_services_amount" => 0,
                "total_supplements_amount" => 0,
                "total_sub_rate_amount" => 0,
                "total_rate_amount" => 0,
                "rooms" => [],
            ];
        }

        // Calcular suplementos
        foreach ($hotels_client as $index_hotel => $hotel_client) {
            foreach ($hotel_client['best_options']['rooms'] as $roomInd => $room) {
                foreach ($room['tarifas_seleccionadas'] as $tarInd => $tarifa) {
                    $supplements = $this->calculateRateSupplementsRequired(
                        $tarifa['rate']["rates_plans_id"],
                        $hotel_client["hotel_id"],
                        $from,
                        $to,
                        $client_id,
                        $tarifa['quantity_adults'],
                        $tarifa['quantity_child'],
                        $tarifa['ages_child'],
                        $tarifa['rate']['markup'],
                        $language_id
                    );

                    $tarifa['total_amount'] += $supplements["total_amount"];
                    $tarifa['supplements'] = $supplements;

                    $hotels_client[$index_hotel]['best_options']['total_rate_amount'] += $supplements["total_amount"];
                    $hotels_client[$index_hotel]['best_options']['total_supplements_amount'] += $supplements["total_amount"];

                    $hotels_client[$index_hotel]['best_options']['rooms'][$roomInd]['total_amount'] += $supplements["total_amount"];

                    $hotels_client[$index_hotel]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd] = $tarifa;
                }
            }
        }

        // Calcular taxes and services
        foreach ($hotels_client as $hotelInd => $hotel_client) {
            // Filtar lo Texes and services que seran aplicadon al hotel segun si el cliente es local o extranjero
            $applicable_fees = $this->getHotelApplicableFees($this->client(), $hotel_client['hotel']);

            foreach ($hotel_client['best_options']['rooms'] as $roomInd => $room) {
                if (empty($hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['total_taxes_and_services_amount'])) {
                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['total_taxes_and_services_amount'] = 0;
                }

                foreach ($room['tarifas_seleccionadas'] as $tarInd => $tarifas_seleccionada) {
                    if (empty($hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['total_taxes_and_services_amount'])) {
                        $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['total_taxes_and_services_amount'] = 0;
                    }

                    $taxes_and_services = $this->addHotelExtraFees($applicable_fees,
                        $tarifas_seleccionada['rate']["rate_plan"], $tarifas_seleccionada["total_amount"]);

                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['total_amount'] += $taxes_and_services['amount_fees'];
                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['taxes_and_services'] = $taxes_and_services;
                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['total_taxes_and_services_amount'] += $taxes_and_services['amount_fees'];

                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['total_amount'] += $taxes_and_services['amount_fees'];
                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['total_taxes_and_services_amount'] += $taxes_and_services['amount_fees'];
                    $hotels_client[$hotelInd]['best_options']['total_taxes_and_services_amount'] += $taxes_and_services['amount_fees'];
                    $hotels_client[$hotelInd]['best_options']['total_rate_amount'] += $taxes_and_services['amount_fees'];
                }
            }
        }

        // Calcular politicas de cancelacion de la mejor seleccion
        foreach ($hotels_client as $hotelInd => $hotel_client) {
            // sacamos la cantidad de habitaciones seleccionadas
            $rooms_quantity = count($hotel_client['best_options']['rooms']);
            // contamos la cantidad total de pasajeros seleccionados
            $guest_quantity = 0;
            foreach ($hotel_client['best_options']['rooms'] as $room) {
                foreach ($room['tarifas_seleccionadas'] as $tarifas_seleccionada) {
                    $guest_quantity += $tarifas_seleccionada['people_coverage'];
                }
            }

            if ($guest_quantity == 0) {
                continue;
            }

            //throw new \Exception(json_encode($hotel_client['best_options']['rooms']));

            foreach ($hotel_client['best_options']['rooms'] as $roomInd => $room) {
                foreach ($room['tarifas_seleccionadas'] as $tarInd => $tarifas_seleccionada) {
                    if ($tarifas_seleccionada['rate']['channel_id'] == 1) {
                        $selected_policies_cancelation = collect($tarifas_seleccionada['rate']["calendarys"][0]["policies_rates"]["policies_cancelation"]);
                    } else {
                        if (count($tarifas_seleccionada['rate']['policies_cancelation']) == 0) {
                            $selected_policies_cancelation = collect($tarifas_seleccionada['rate']["calendarys"][0]["policies_cancelation"]);
                        } else {
                            $selected_policies_cancelation = collect($tarifas_seleccionada['rate']['policies_cancelation']);
                        }
                    }

                    $policy_cancellation_calculated = $this->calculateCancellationlPolicies($current_date,
                        $check_in, $check_out, $tarifas_seleccionada["total_amount"],
                        $selected_policies_cancelation, $guest_quantity, $rooms_quantity);

                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['policy_cancellation'] = $policy_cancellation_calculated['next_penalty'];
                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['policies_cancellation'] = $policy_cancellation_calculated['penalties'];
                }
            }
        }

        //Agregar las tarifas On Request
        $rates_on_request = [];
        if (isset($rates_plan_rooms_on_request) && $clientAllowedOnRequest) {
            $rates_on_request = $this->getClientHotelsAvailOnRequest(
                $period,
                $from,
                $to,
                $reservation_days,
                $hotels_id,
                $language_id,
                $rates_plan_rooms_on_request
            );
        }

        // Obtengo un listado de hoteles que no esten incluidos en $hotels_client
        $hotels_id_onrequest = array();
        foreach ($rates_on_request as $rate) {
            $sw = false;
            foreach ($hotels_client as $hotel_client) {
                if ($rate["room"]["hotel_id"] == $hotel_client["hotel"]["id"]) {
                    $sw = true;
                }
            }
            if (!$sw) {
                $hotels_id_onrequest[$rate["room"]["hotel_id"]] = $rate["room"]["hotel_id"];
            }
        }

        if (count($hotels_id_onrequest) > 0) {
            $hotels_client_on_request = $this->getDataHotelNoReturn(
                $hotels_id_onrequest,
                $period,
                $client_id,
                $date_from,
                $date_to,
                $language->id,
                $from,
                $typeclass_id,
                $country_id
            );
            $merged = array_merge($hotels_client, $hotels_client_on_request);
            $hotels_client = $this->deepToArray($merged);
        }

        //validamos si hay habitaciones onRequest que no esten agregados a la matriz de los hoteles y las creamos pero si existen ya las agregamos
        foreach ($rates_on_request as $index_rate => $rate) {
            if ($rate["status"] != 1) {
                continue;
            }
            foreach ($hotels_client as $index_hotel => $hotel_client) {
                if ($rate["room"]["hotel_id"] == $hotel_client["hotel"]["id"]) {

                    $key = in_array($rate['room_id'], array_column($hotel_client["hotel"]["rooms"], 'id'));

                    if ($key === false) {
                        $rates_on_request[$index_rate]["status"] = 0;
                        $rate["status"] = 0;
                        $rate["room"]['rates_plan_room'][] = $rate;
                        $hotels_client[$index_hotel]["hotel"]["rooms"][] = $rate["room"];
                        break;
                    } else {
                        foreach ($hotel_client["hotel"]["rooms"] as $index_room => $room) {
                            if ($rate["room_id"] == $room["id"]) {
                                $rates_on_request[$index_rate]["status"] = 0;
                                $rate["status"] = 0;
                                //$hotel_client["hotel"]["rooms"][$index_room]["rates_plan_room"][] = $rate;
                                $hotels_client[$index_hotel]["hotel"]["rooms"][$index_room]["rates_plan_room"][] = $rate;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // $this->getHotelAvailAmadeus();
        $faker = Faker::create();
        $token_search = $faker->unique()->uuid;

        $this->storeTokenSearchHotels($token_search, $hotels_client, $this->expiration_search_hotels);
        //procedimiento para estructurar json con los datos necesarios
        $hotels = [
            [
                "city" => [
                    "token_search" => $token_search,
                    "token_search_frontend" => "",
                    "ids" => $destiny["code"] ?? '',
                    "description" => $destiny["label"] ?? '',
                    "class" => [],
                    "zones" => [],
                    "hotels" => [],
                    "search_parameters" => $search_parameters,
                    "quantity_hotels" => 0,
                ],
            ],
        ];

        $min_price_search = 0;
        $max_price_search = 0;

        // return $hotels_client; die;

        foreach ($hotels_client as $index => $hotel_client) {
            $hotel_description = "";
            $hotel_address = "";
            $hotel_logo = "";
            $hotel_summary = "";
            $hotel_notes = "";
            $amenities = [];
            $hotel_gallery = [];
            $rooms = [];

            $best_options = [];

            if (count($hotel_client["best_options"]) > 0) {
                $best_options = [
                    "quantity_rooms" => $hotel_client["best_options"]["quantity_rooms"],
                    "quantity_adults" => $hotel_client["best_options"]["quantity_adults"],
                    "quantity_child" => $hotel_client["best_options"]["quantity_child"],
                    "total_taxes_and_services_amount" => roundLito((float)number_format($hotel_client["best_options"]["total_taxes_and_services_amount"],
                        2, '.', '')),
                    "total_supplements_amount" => roundLito((float)number_format($hotel_client["best_options"]["total_supplements_amount"],
                        2, '.', '')),
                    "total_sub_rate_amount" => roundLito((float)number_format($hotel_client["best_options"]["total_sub_rate_amount"],
                        2, '.', '')),
                    "total_rate_amount" => roundLito((float)number_format($hotel_client["best_options"]["total_rate_amount"],
                        2,
                        '.', '')),
                    "rooms" => [],
                ];
            }

            /*if(empty($destiny["code"])){
               $hotelsQ = Hotel::whereIn('id', $hotels_id)->first();

               $country_id = $hotelsQ->country_id;
               $state_id = $hotelsQ->state_id;
               $city_id = $hotelsQ->city_id;
               $district_id = $hotelsQ->district_id;
            }*/

            //Agregar Arreglo de Zonas de Hoteles a la Busqueda
            if ($city_id != "") {
                $zones = Zone::where('city_id', $city_id)->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language->id);
                    },
                ])->get();
                foreach ($zones as $zone) {
                    $find_zone = false;
                    if (count($hotels[0]["city"]["zones"]) == 0) {
                        $hotels[0]["city"]["zones"][] = [
                            "zone_name" => $zone["translations"][0]["value"] ?? '',
                            "status" => false,
                        ];
                    } else {
                        foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                            if ($zone_new["zone_name"] == ($zone["translations"][0]["value"] ?? '')) {
                                $find_zone = true;
                            }
                        }
                        if (!$find_zone) {
                            $hotels[0]["city"]["zones"][] = [
                                "zone_name" => $zone["translations"][0]["value"] ?? '',
                                "status" => false,
                            ];
                        }
                    }
                }
            } else {
                if (!empty($state_id)) {
                    $state = State::where('iso', $state_id)->first();
                    $cities = City::where('state_id', $state->id)->get();
                    foreach ($cities as $city) {
                        $zones = Zone::where('city_id', $city["id"])->with([
                            'translations' => function ($query) use ($language) {
                                $query->where('language_id', $language->id);
                            },
                        ])->get();
                        foreach ($zones as $zone) {
                            $find_zone = false;
                            if (count($hotels[0]["city"]["zones"]) == 0) {
                                $hotels[0]["city"]["zones"][] = [
                                    "zone_name" => $zone["translations"][0]["value"] ?? '',
                                    "status" => false,
                                ];
                            } else {
                                foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                                    if ($zone_new["zone_name"] == ($zone["translations"][0]["value"] ?? '')) {
                                        $find_zone = true;
                                    }
                                }
                                if (!$find_zone) {
                                    $hotels[0]["city"]["zones"][] = [
                                        "zone_name" => $zone["translations"][0]["value"] ?? '',
                                        "status" => false,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            $hoteltypeclass = $this->getHotelTypeClass($hotel_client["hotel"]["hoteltypeclass"]);

            //Agregar Arreglo de Clases de Hoteles a la Busqueda
            if (count($hotels[0]["city"]["class"]) == 0) {

                // dd(json_encode($hotel_client["hotel"]));
                $hotels[0]["city"]["class"][] = [
                    "class_name" => $hoteltypeclass['name'], //$hotel_client["hotel"]["typeclass"]["translations"][0]["value"],
                    "status" => false,
                ];
            } else {
                $find_class = false;
                foreach ($hotels[0]["city"]["class"] as $class) {
                    if ($class["class_name"] == $hoteltypeclass['name']) { //$hotel_client["hotel"]["typeclass"]["translations"][0]["value"]) {
                        $find_class = true;
                    }
                }
                if (!$find_class) {
                    $hotels[0]["city"]["class"][] = [
                        "class_name" => $hoteltypeclass['name'], //$hotel_client["hotel"]["typeclass"]["translations"][0]["value"],
                        "status" => false,
                    ];
                }
            }

            //Agregar Precio Maximo y Minimo de hotel a Busqueda
            if (count($hotel_client["best_options"]) > 0) {
                if ($index === 0) {
                    $min_price_search = $hotel_client["best_options"]["total_rate_amount"];
                    $max_price_search = $hotel_client["best_options"]["total_rate_amount"];
                } else {
                    if ($hotel_client["best_options"]["total_rate_amount"] < $min_price_search) {
                        $min_price_search = $hotel_client["best_options"]["total_rate_amount"];
                    }
                    if ($hotel_client["best_options"]["total_rate_amount"] > $max_price_search) {
                        $max_price_search = $hotel_client["best_options"]["total_rate_amount"];
                    }
                }
            }

            $hotel_summary = "";
            $hotel_notes = "";

            if (!empty($hotel_client['hotel']['alerts'])) {
                // Clavea por language_id
                $alerts = collect($hotel_client['hotel']['alerts'])->keyBy('language_id');

                $alertLang1 = $alerts->get(1);                 // puede ser array u objeto o null
                $alertLangN = $alerts->get((int)$language_id); // idem

                // 1) REMARKS: siempre del idioma 1
                $hotel_notes = data_get($alertLang1, 'remarks'); // <- maneja array/objeto/null

                // 2) NOTES (summary)
                if ((int)$language_id === 1) {
                    $hotel_summary = data_get($alertLang1, 'notes');
                } else {
                    // intenta en el idioma solicitado y, si no hay, fallback opcional al 1
                    $hotel_summary = data_get($alertLangN, 'notes', data_get($alertLang1, 'notes'));
                    // Si NO quieres fallback, usa solo: data_get($alertLangN, 'notes')
                }
            }


            //cargar traducciones
            foreach ($hotel_client["hotel"]["translations"] as $translation) {
                if ($translation["slug"] === "hotel_address") {
                    $hotel_address = $translation["value"];
                }
                if ($translation["slug"] === "hotel_description") {
                    $hotel_description = $translation["value"];
                }
                // if ($translation["slug"] === "summary") {
                //     $hotel_summary = $translation["value"];
                // }
                // if ($translation["slug"] === "notes") {
                //     $hotel_notes = $translation["value"];
                // }
            }

            //cargar galeria de hotel
            foreach ($hotel_client["hotel"]["galeries"] as $image) {
                if ($image["slug"] === "hotel_logo") {
                    $hotel_logo = secure_url('/') . '/images/' . $image["url"];
                }
                if ($image["slug"] === "hotel_gallery") {
                    $find_cloudinary = strpos($image["url"], "cloudinary");
                    if (!$find_cloudinary) {
                        $hotel_gallery[] = url('/') . '/images/' . $image["url"];
                    } else {
                        $hotel_gallery[] = $image["url"];
                    }
                }
            }

            //cargar amenities de hotel
            if (count($hotel_client["hotel"]["amenity"]) > 0) {
                foreach ($hotel_client["hotel"]["amenity"] as $amenity) {
                    $amenities[] = [
                        "name" => $amenity["translations"][0]["value"] ?? 'N/A',
                        "image" => count($amenity["galeries"]) > 0 ? secure_url('/') . '/images/' . $amenity["galeries"][0]["url"] : '',
                    ];
                }
            }

            // Filtar lo Texes and services que seran aplicadon al hotel segun si el cliente es local o extranjero
            $applicable_fees = $this->getHotelApplicableFees($this->client(), $hotel_client['hotel']);

            //cargar habitaciones de hotel
            if (!count($quantity_persons_rooms)) {
                $quantity_persons_rooms[] = false;
            }

            foreach ($quantity_persons_rooms as $quantity_persons_room) {
                foreach ($hotel_client["hotel"]["rooms"] as $room) {

                    // Verificar que no se repitan habitaciones
                    $exists = collect($rooms)->contains('room_id', $room['id']);
                    if ($exists) {
                        continue;
                    }

                    if (!$quantity_persons_room) {
                        $quantity_persons_room_new = [
                            'adults' => $room["max_adults"],
                            'child' => 0,
                            'ages_child' => [],
                        ];
                    } else {
                        $quantity_persons_room_new = $quantity_persons_room;
                    }

                    $room_name = "";
                    $room_description = "";
                    $room_gallery = [];
                    $rates = [];
                    //cargar traducciones de habitacion
                    foreach ($room["translations"] as $translation) {
                        if ($translation["slug"] == "room_name") {
                            $room_name = $translation["value"];
                        }
                        if ($translation["slug"] == "room_description") {
                            $room_description = $translation["value"];
                        }
                    }
                    //cargar galeria de habitacion
                    foreach ($room["galeries"] as $image) {
                        $room_gallery[] = $image["url"];
                    }
                    //cargar tarifas de habitacion
                    $ids_rates_channels_charged = [];

                    // if(count($hotel_client["hotel"]["rooms"]) > 0){
                    // $ids_rates_channels_charged = $this->getIdsRatesChannelsCharged($hotel_client['hotel']['id'], $from, $to, $room['id']);
// Corregir aquiiiii alexxxxxxxxxxxxx
                    $rate_plan_rooms = $room["rates_plan_room"];
                    // }else{
                    //     $rate_plan_rooms = $room["rates_plan_room"]->toArray();
                    // }

                    foreach ($rate_plan_rooms as $rate) {

                        if (empty($rate["calendarys"]) || !isset($rate["calendarys"][0])) {
                            continue;
                        }

                        //calculo de inventario disponible dado un rango de fechas
                        $min_inventory = 0;
                        if (count($rate["inventories"]) == 0 && $rate["bag"] == 1) {
                            if (isset($rate["bag_rate"])) {
                                $rate["inventories"] = $rate["bag_rate"]["bag_room"]["inventory_bags"];
                            }
                        }

                        if ($room['inventory'] == 1 || strtoupper($rate['channel']['code']) === 'HYPERGUEST') {
                            foreach ($rate["inventories"] as $index => $inventory) {
                                if ($index === 0) {
                                    $min_inventory = $inventory["inventory_num"];
                                } else {
                                    if ($inventory["inventory_num"] < $min_inventory) {
                                        $min_inventory = $inventory["inventory_num"];
                                    }
                                }
                            }
                        }

                        if ($rate["status"] == 0) {
                            $min_inventory = 10;
                        }
                        //--------------------------------------------------calculo de importes para 1 adulto----------------------------------------------------------------

                        $check_other_step = true;

                        $markupFromSearch = $this->getMarkupFromsearch(
                            $hotel_client['client_markup'],
                            $hotel_client['hotel']['markup'],
                            $rate,
                            $setMarkup
                        );
                        if ($rate["channel_id"] == 1) {
                            $rates_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                                $markupFromSearch,
                                $quantity_persons_room_new["adults"],
                                $quantity_persons_room_new["child"],
                                0,
                                $quantity_persons_room_new["ages_child"],
                                $hotel_client['hotel'],
                                $room["bed_additional"],
                                $room
                            );
                        } else {
                            if (in_array($rate["id"], $ids_rates_channels_charged)) {
                                continue;
                            }

                            if (strtoupper($rate['channel']['code']) === 'HYPERGUEST') {
                                $rates_plan_room_new = $this->getChannelsAvailableRates(
                                    $markupFromSearch,
                                    $quantity_persons_room_new["adults"],
                                    $quantity_persons_room_new["child"],
                                    $quantity_persons_room_new["ages_child"],
                                    $hotel_client['hotel'],
                                    $room
                                );
                            } else {
                                $rates_plan_room_new = $this->getChannelsFirstAvailRate(
                                    $this->getMarkupFromsearch($hotel_client['client_markup'],
                                        $hotel_client['hotel']['markup'],
                                        $rate, $setMarkup),
                                    $rate["id"]);
                            }
                        }

                        if (!$zeroRates && $rates_plan_room_new["total_amount"] <= 0) {
                            continue;
                        }

                        if (!$zeroRates && $rates_plan_room_new["total_amount"] <= 0) {
                            continue;
                        }

                        if ($rates_plan_room_new && $check_other_step) {
                            $guest_quantity = 1;
                            $rooms_quantity = 1;

                            //calculo de detalle de politicas de cancelacion
                            if ($rate["channel_id"] == 1) {
                                if (count($rates_plan_room_new["calendarys"] ?? []) == 0) {
                                    throw new \Exception('No availability for selected dates');
                                }
                                $selected_policies_cancelation = collect($rates_plan_room_new["calendarys"][0]["policies_rates"]["policies_cancelation"] ?? []);
                            } else {
                                if (count($rates_plan_room_new['policies_cancelation'] ?? []) == 0) {
                                    $selected_policies_cancelation = collect($rates_plan_room_new["calendarys"][0]["policies_cancelation"] ?? []);
                                } else {
                                    $selected_policies_cancelation = collect($rates_plan_room_new["policies_cancelation"]);
                                }
                            }
                            $no_show_apply = [
                                "executive" => Auth::user()->user_type_id,
                                "message" => "",
                            ];
                            if ($rates_plan_room_new["total_amount_child"] == 0) {
                                $no_show_apply["political_child"] = "Free";
                            } else {
                                $no_show_apply["political_child"] = "Cost";
                            }
                            if (count($selected_policies_cancelation) > 0) {

                                $policy_cancellation_id = null;
                                if (!isset($selected_policies_cancelation[0])) {
                                    $policy_cancellation_id = $selected_policies_cancelation["id"];
                                } else {
                                    $policy_cancellation_id = $selected_policies_cancelation[0]["id"];
                                }
                            }

                            $supplements = $this->calculateRateSupplementsRequired($rate["rates_plans_id"],
                                $hotel_client["hotel_id"], $from, $to, $client_id, 1, 0, [],
                                $rates_plan_room_new['markup'], $language_id);

                            $policy_cancellation_calculated = $this->calculateCancellationlPolicies($current_date,
                                $check_in, $check_out,
                                $rates_plan_room_new["total_amount"] + $supplements["total_amount"],
                                $selected_policies_cancelation, $guest_quantity, $rooms_quantity);

                            $message = empty($policy_cancellation_calculated['next_penalty']["message"]) ? '' : $policy_cancellation_calculated['next_penalty']["message"];

                            $taxes_and_services = $this->addHotelExtraFees($applicable_fees, $rate["rate_plan"],
                                ($rates_plan_room_new["total_amount"] + $supplements["total_amount"]));

                            $total_amount = $rates_plan_room_new["total_amount"] + $taxes_and_services["amount_fees"] + $supplements["total_amount"];
                            $total_amount_tax = $taxes_and_services["amount_fees"];

                            $total_amount = number_format($total_amount, 2, '.', '');
                            $total_amount_tax = number_format($total_amount_tax, 2, '.', '');

                            $rates_calendars = $rates_plan_room_new["calendarys"];

                            $flat_migrate = 0;
                            foreach ($rates_calendars as $idratecalen => $rates_calendar) {
                                unset($rates_calendars[$idratecalen]['policies_rates']['translations']);

                                $date_day = $rates_calendars[$idratecalen]['date'];
                                if (isset($rates_calendars[$idratecalen]['date'])) {
                                    $date_range_hotel = DateRangeHotel::where('hotel_id', $hotel_client['hotel_id'])
                                        ->whereDate('date_from', '<=', $date_day)
                                        ->whereDate('date_to', '>=', $date_day)
                                        ->where('rate_plan_room_id', $rates_calendars[$idratecalen]['rates_plans_room_id'])->first();

                                    if ($date_range_hotel) {
                                        if ($date_range_hotel['flag_migrate'] == 1) {
                                            $flat_migrate = 1;
                                        }
                                    }
                                }
                            }


                            $cancellation_details = [];
                            if (isset($policy_cancellation_calculated['selected_policy_cancelation']["policy_cancellation_parameter"])) {
                                foreach ($policy_cancellation_calculated['selected_policy_cancelation']["policy_cancellation_parameter"] as $detail) {
                                    $cancellation_details[] = [
                                        'to' => $detail["min_day"],
                                        'from' => $detail["max_day"],
                                        'amount' => $detail["amount"] ? $detail["amount"] : 100,
                                        'tax' => $detail["tax"],
                                        'service' => $detail["service"],
                                        'penalty' => $detail["penalty"]["name"],
                                    ];
                                }
                            } else {
                                $cancellation_details[] = [
                                    'to' => 0,
                                    'from' => 0,
                                    'amount' => 100,
                                    'tax' => 1,
                                    'service' => 1,
                                    'penalty' => 'total_reservation ',
                                ];
                            }

                            if ($rate["channel_id"] == 1) {
                                $max_occupancy = $rate["calendarys"][0]["policies_rates"]["max_occupancy"] ?? 0;
                            } else {
                                $max_occupancy = $rate["calendarys"][0]["max_occupancy"] ?? 0;
                            }

                            $rate_plan_description = '';
                            if ($rate['descriptions']) {
                                $rate_plan_description = $rate['descriptions'][0]['value'];
                            }

                            //Agregar tarifa al arreglo
                            if ($rate["channel_id"] == 1) {
                                $promotions_data_ = [];
                                if (isset($rate["rate_plan"]["promotions_data"]) && $rate["rate_plan"]["promotions"]) {
                                    $promotions_data_ = $rate["rate_plan"]["promotions_data"];
                                } else {
                                    if ($rate["rate_plan"]["promotions"]) {
                                        $promotions_data_ =
                                            RatesPlansPromotions::where('rates_plans_id',
                                                $rate["rate_plan"]["id"])->get();
                                    }
                                }

                                $rate_name = (count($rate["rate_plan"]["translations"]) > 0) ? $rate["rate_plan"]["translations"][0]["value"] : '';
                                $no_show = (count($rate["rate_plan"]["translations_no_show"]) > 0) ? $rate["rate_plan"]["translations_no_show"][0]["value"] : '';
                                $day_use = (count($rate["rate_plan"]["translations_day_use"]) > 0) ? $rate["rate_plan"]["translations_day_use"][0]["value"] : '';
                                $notes = (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes';

                                $rates[] = [
                                    'rateId' => $rate["id"],
                                    'ratePlanId' => $rate["rate_plan"]["id"],
                                    'rates_plans_type_id' => $rate["rate_plan"]["rates_plans_type_id"],
                                    'promotions_data' => $promotions_data_,
                                    'name_commercial' => $rate["rate_plan"]["name"],
                                    'name' => $rate_name,
                                    'description' => $rate_plan_description,
                                    'meal_id' => $rate["rate_plan"]["meal"]['id'],
                                    'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"] ?? 'N/A',
                                    'available' => $min_inventory,
                                    'onRequest' => $rate["status"],
                                    'rateProvider' => $rate["channel"]["name"],
                                    'rateProviderMethod' => NULL,
                                    'no_show' => $no_show,
                                    'day_use' => $day_use,
                                    'notes' => $notes,
                                    'total' => $total_amount,
                                    'total_taxes_and_services' => $total_amount_tax,
                                    'avgPrice' => $total_amount,
                                    'political' => [
                                        'rate' => [
                                            'name' => $rate["calendarys"][0]["policies_rates"]["name"] ?? 'N/A',
                                            'message' => (isset($rate["calendarys"][0]["policies_rates"]["translations"]) ? ($rate["calendarys"][0]["policies_rates"]["translations"][0]['value'] ?? '') : ''),
                                            'max_occupancy' => $max_occupancy,
                                            'example' => $rate,
                                        ],
                                        'cancellation' => [
                                            "name" => $message,
                                            "details" => $cancellation_details,
                                        ],
                                        "no_show_apply" => $no_show_apply,
                                    ],
                                    'quantity_rates' => 1,
                                    'taken' => false,
                                    'cart_items_id' => [],
                                    'disabled_buttons' => false,
                                    'taxes_and_services' => $taxes_and_services,
                                    'supplements' => $supplements,
                                    'rate' => [
                                        [
                                            'total_amount' => $total_amount,
                                            'total_taxes_and_services' => $total_amount_tax,
                                            'avgPrice' => $total_amount,
                                            'quantity_adults' => $rates_plan_room_new['quantity_adults'],
                                            'quantity_child' => $rates_plan_room_new['quantity_child'],
                                            'ages_child' => $rates_plan_room_new["ages_child"],
                                            'quantity_extras' => $rates_plan_room_new['quantity_extras'],
                                            'quantity_adults_total' => $rates_plan_room_new['quantity_adults'],
                                            'quantity_child_total' => $rates_plan_room_new['quantity_child'],
                                            'quantity_extras_total' => $rates_plan_room_new['quantity_extras'],
                                            'total_amount_adult' => $rates_plan_room_new['total_amount_adult'],
                                            'total_amount_child' => $rates_plan_room_new['total_amount_child'],
                                            'total_amount_infants' => 0,
                                            'total_amount_extras' => $rates_plan_room_new['total_amount_extra'],
                                            'people_coverage' => $rates_plan_room_new['quantity_adults'] + $rates_plan_room_new['quantity_child'],
                                            'quantity_inventory_taken' => 1,
                                            'amount_days' => $rates_calendars,
                                            'flag_migrate' => $flat_migrate,
                                        ],
                                    ],
                                    'show_message_error' => $rates_plan_room_new['show_message_error'],
                                    'message_error' => $rates_plan_room_new['message_error'],
                                ];
                            } else {
                                if (!in_array($rate["id"], $ids_rates_channels_charged)) {
                                    $promotions_data_ = [];
                                    if (isset($rate["rate_plan"]["promotions_data"]) && $rate["rate_plan"]["promotions"]) {
                                        $promotions_data_ = $rate["rate_plan"]["promotions_data"];
                                    } else {
                                        if ($rate["rate_plan"]["promotions"]) {
                                            $promotions_data_ =
                                                RatesPlansPromotions::where('rates_plans_id',
                                                    $rate["rate_plan"]["id"])->get();
                                        }
                                    }
                                    $rate_name = (count($rate["rate_plan"]["translations"]) > 0) ? $rate["rate_plan"]["translations"][0]["value"] : '';
                                    $no_show = (count($rate["rate_plan"]["translations_no_show"]) > 0) ? $rate["rate_plan"]["translations_no_show"][0]["value"] : '';
                                    $day_use = (count($rate["rate_plan"]["translations_day_use"]) > 0) ? $rate["rate_plan"]["translations_day_use"][0]["value"] : '';
                                    $notes = (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes';

                                    $rateProviderMethod = NULL;
                                    if ($rate["channel"]["name"] == "HYPERGUEST") {
                                        $rateProviderMethod = $rate["rate_plan"]['type_channel'];
                                    }
                                    $rates[] = [
                                        'rateId' => $rate["id"],
                                        'ratePlanId' => $rate["rate_plan"]["id"],
                                        'rates_plans_type_id' => $rate["rate_plan"]["rates_plans_type_id"],
                                        'promotions_data' => $promotions_data_,
                                        'name_commercial' => $rate["rate_plan"]["name"],
                                        'name' => $rate_name,
                                        'description' => $rate_plan_description,
                                        'meal_id' => $rate["rate_plan"]["meal"]['id'],
                                        'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"],
                                        'available' => $min_inventory,
                                        'onRequest' => $rate["status"],
                                        'rateProvider' => $rate["channel"]["name"],
                                        'rateProviderMethod' => $rateProviderMethod,
                                        'no_show' => $no_show,
                                        'day_use' => $day_use,
                                        'notes' => $notes,
                                        'total' => $total_amount,
                                        'total_taxes_and_services' => $total_amount_tax,
                                        'avgPrice' => $total_amount,
                                        'political' => [
                                            'rate' => [
                                                'name' => $rate["calendarys"][0]["policies_rates"]["name"],
                                                'message' => (isset($rate["calendarys"][0]["policies_rates"]["translations"]) ? $rate["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
                                                'max_occupancy' => $max_occupancy,
                                                'example' => $rate,
                                            ],
                                            'cancellation' => [
                                                "name" => $message,
                                                "details" => $cancellation_details,
                                            ],
                                            "no_show_apply" => $no_show_apply,
                                        ],
                                        'quantity_rates' => 1,
                                        'taken' => false,
                                        'cart_items_id' => [],
                                        'disabled_buttons' => false,
                                        'taxes_and_services' => $taxes_and_services,
                                        'supplements' => $supplements,
                                        'rate' => [
                                            [
                                                'total_amount' => $total_amount,
                                                'total_taxes_and_services' => $total_amount_tax,
                                                'avgPrice' => $total_amount,
                                                'quantity_adults' => $rates_plan_room_new['quantity_adults'],
                                                'quantity_child' => $rates_plan_room_new['quantity_child'],
                                                'ages_child' => $rates_plan_room_new["ages_child"],
                                                'quantity_extras' => $rates_plan_room_new['quantity_extras'],
                                                'quantity_adults_total' => $rates_plan_room_new['quantity_adults'],
                                                'quantity_child_total' => $rates_plan_room_new['quantity_child'],
                                                'quantity_extras_total' => $rates_plan_room_new['quantity_extras'],
                                                'total_amount_adult' => $rates_plan_room_new['total_amount_adult'],
                                                'total_amount_child' => $rates_plan_room_new['total_amount_child'],
                                                'total_amount_infants' => 0,
                                                'total_amount_extras' => $rates_plan_room_new['total_amount_extra'],
                                                'people_coverage' => $rates_plan_room_new['quantity_adults'] + $rates_plan_room_new['quantity_child'],
                                                'quantity_inventory_taken' => 1,
                                                'amount_days' => $rates_calendars,
                                            ],
                                        ],
                                        'show_message_error' => $rates_plan_room_new['show_message_error'],
                                        'message_error' => $rates_plan_room_new['message_error'],
                                    ];
                                    $ids_rates_channels_charged[] = $rate["id"];
                                }
                            }
                        }
                    }

                    if (count($rates) == 0) {
                        continue;
                    }
                    // $rates_jc = $rates;
                    $ratesConfirmed = collect($rates)
                        ->filter(function ($rate, $key) {
                            return $rate['total'] > 0 && $rate['onRequest'] === 1;
                        })
                        ->sortBy('total')
                        ->values();

                    $ratesOnRequest = collect($rates)
                        ->filter(function ($rate, $key) {
                            return $rate['total'] > 0 && $rate['onRequest'] === 0;
                        })
                        ->sortBy('total')
                        ->values();

                    $ratesZero = collect($rates)
                        ->filter(function ($rate, $key) {
                            return $rate['total'] == 0;
                        })
                        ->sortBy(function ($rate, $key) {
                            return $rate['onRequest'] ? 0 : 1;
                        })
                        ->values();

                    $rates = $ratesConfirmed->merge($ratesOnRequest)->merge($ratesZero);

                    // Habilitar solo tarifas de HYPERGUEST
                    $rates = $this->onlyRatesHyperguestAndAuroraHotels($rates->toArray());

                    //agregar habitacion al arreglo
                    $rooms[] = [
                        'room_id' => $room["id"],
                        'room_type_id' => $room["room_type_id"],
                        'room_type' => $room['room_type']['translations'][0]['value'] ?? 'N/A',
                        'occupation' => $room['room_type']['occupation'] ?? 0,
                        'bed_additional' => $room["bed_additional"],
                        'name' => $room_name,
                        'description' => $room_description,
                        'gallery' => $room_gallery ?? [],
                        'max_capacity' => $room["max_capacity"],
                        'max_adults' => $room["max_adults"],
                        'max_child' => $room["max_child"] ?? 0,
                        'best_price' => $rates[0]['total'] ?? '0.00',
                        'rates' => $rates,
                    ];


                }
            }

            if (!count($rooms)) {
                continue;
            }

            if (count($hotel_client["best_options"]) > 0) {
                // sacamos la cantidad de habitaciones seleccionadas
                $rooms_quantity = count($hotel_client['best_options']['rooms']);
                // contamos la cantidad total de pasajeros seleccionados
                $guest_quantity = 0;
                foreach ($hotel_client['best_options']['rooms'] as $room) {
                    foreach ($room['tarifas_seleccionadas'] as $tarifas_seleccionada) {
                        $guest_quantity += $tarifas_seleccionada['people_coverage'];
                    }
                }

                if ($guest_quantity > 0) {
                    //cargar mejor opciones de hotel
                    foreach ($hotel_client["best_options"]["rooms"] as $opcion) {
                        $room_name = "";
                        $room_description = "";
                        $room_gallery = [];
                        $rates = [];
                        //cargar traducciones de habitacion
                        foreach ($opcion["translations"] as $translation) {
                            if ($translation["slug"] == "room_name") {
                                $room_name = $translation["value"];
                            }
                            if ($translation["slug"] == "room_description") {
                                $room_description = $translation["value"];
                            }
                        }
                        //cargar galeria de habitacion
                        foreach ($opcion["galeries"] as $image) {
                            $room_gallery[] = $image["url"];
                        }

                        //cargar tarifas de habitacion
                        foreach ($opcion["tarifas_seleccionadas"] as $rate) {
                            //calculo de inventario disponible dado un rango de fechas
                            $min_inventory = 0;

                            if (count($rate["rate"]["inventories"]) == 0) {
                                if (isset($rate["rate"]["bag_rate"])) {
                                    $rate["rate"]["inventories"] = $rate["rate"]["bag_rate"]["bag_room"]["inventory_bags"];
                                }
                            }

                            foreach ($rate["rate"]["inventories"] as $index => $inventory) {

                                if ($index === 0) {
                                    $min_inventory = $inventory["inventory_num"];
                                } else {
                                    if ($inventory["inventory_num"] < $min_inventory) {
                                        $min_inventory = $inventory["inventory_num"];
                                    }
                                }
                            }

                            //importes de tarifa
                            $rates_calendars = [];
                            foreach ($rate["rate"]["calendarys"] as $index => $calendary) {
                                $rates_calendars[] = [
                                    'day' => Carbon::parse($calendary['date'])->format('d-m-Y'),
                                    'total_amount' => $calendary['rate'][0]['total_amount'],
                                    'total_adult' => $calendary['rate'][0]['total_adult'],
                                    'total_child' => $calendary['rate'][0]['total_child'],
                                    'total_extra' => $calendary['rate'][0]['total_extra'],
                                    'price_total' => $calendary['rate'][0]['price_total'],
                                    'price_adult' => $calendary['rate'][0]['price_adult'],
                                    'price_child' => $calendary['rate'][0]['price_child'],
                                    'price_infant' => $calendary['rate'][0]['price_infant'],
                                    'price_extra' => $calendary['rate'][0]['price_extra'],
                                ];
                            }

                            //calculo de detalle de politicas de cancelacion
                            if ($rate['rate']['channel_id'] == 1) {
                                $selected_policies_cancelation = collect($tarifas_seleccionada['rate']["calendarys"][0]["policies_rates"]["policies_cancelation"] ?? []);
                            } else {
                                if (count($tarifas_seleccionada['rate']['policies_cancelation'] ?? []) == 0) {
                                    $selected_policies_cancelation = collect($tarifas_seleccionada['rate']["calendarys"][0]["policies_cancelation"] ?? []);
                                } else {
                                    $selected_policies_cancelation = collect($tarifas_seleccionada['rate']['policies_cancelation']);
                                }
                            }

                            $selected_policy_cancelation = $this->getCancellationPolicyByTypeFit($selected_policies_cancelation,
                                $guest_quantity, $rooms_quantity);

                            $cancellation_details = [];
                            if (isset($selected_policy_cancelation["policy_cancellation_parameter"])) {
                                foreach ($selected_policy_cancelation["policy_cancellation_parameter"] as $detail) {
                                    $cancellation_details[] = [
                                        'to' => $detail["min_day"],
                                        'from' => $detail["max_day"],
                                        'amount' => $detail["amount"],
                                        'tax' => $detail["tax"],
                                        'service' => $detail["service"],
                                    ];
                                }
                            }

                            $message = $rate["policy_cancellation"]["message"];

                            //                            if ($rate["rate"]["channel_id"] == 1) {
                            //                                $message = $rate["policy_cancellation"]["message"];
                            //                            } else {
                            //                                $message = "";
                            //                            }

                            $rate_plan_description = '';
                            if ($rate['rate']['descriptions'] && isset($rate['rate']['descriptions'][0])) {
                                $rate_plan_description = $rate['rate']['descriptions'][0]['value'];
                            }

                            $total_amount = number_format($rate["total_amount"], 2, '.', '');
                            $total_amount_tax = number_format($rate["total_taxes_and_services_amount"], 2, '.', '');

                            //                            $promotions_data_ =
                            //                                (isset($rate["rate"]["rate_plan"]["promotions_data"]) && $rate["rate"]["rate_plan"]["promotions"])
                            //                                    ? $rate["rate"]["rate_plan"]["promotions_data"] : [];

                            $promotions_data_ = [];
                            if (isset($rate["rate"]["rate_plan"]["promotions_data"]) && $rate["rate"]["rate_plan"]["promotions"]) {
                                $promotions_data_ = $rate["rate"]["rate_plan"]["promotions_data"];

                            } else {
                                if ($rate["rate"]["rate_plan"]["promotions"]) {
                                    $promotions_data_ =
                                        RatesPlansPromotions::where('rates_plans_id',
                                            $rate["rate"]["rate_plan"]["id"])->get();
                                }
                            }

                            $rate_name = (count($rate["rate_plan"]["translations"]) > 0) ? $rate["rate_plan"]["translations"][0]["value"] : '';
                            $no_show = (count($rate["rate_plan"]["translations_no_show"]) > 0) ? $rate["rate_plan"]["translations_no_show"][0]["value"] : '';
                            $day_use = (count($rate["rate_plan"]["translations_day_use"]) > 0) ? $rate["rate_plan"]["translations_day_use"][0]["value"] : '';
                            $notes = (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes';
                            $rateProviderMethod = NULL;
                            if ($rate["rate"]["channel"]["name"] == "HYPERGUEST") {
                                $rateProviderMethod = $rate["rate_plan"]['type_channel'];
                            }
                            //Agregar tarifa al arreglo
                            $rates[] = [
                                'rateId' => $rate["rate"]["id"],
                                'ratePlanId' => $rate["rate"]["rate_plan"]["id"],
                                'rates_plans_type_id' => $rate["rate"]["rate_plan"]["rates_plans_type_id"],
                                'promotions_data' => $promotions_data_,
                                'name_commercial' => $rate["rate"]["rate_plan"]["name"],
                                'name' => $rate_name,
                                'description' => $rate_plan_description,
                                'meal_id' => $rate["rate"]["rate_plan"]["meal"]['id'],
                                'meal_name' => $rate["rate"]["rate_plan"]["meal"]["translations"][0]["value"],
                                'available' => $min_inventory,
                                'inventories' => $rate["rate"]["inventories"],
                                'onRequest' => 1,
                                'rateProvider' => $rate["rate"]["channel"]["name"],
                                'rateProviderMethod' => $rateProviderMethod,
                                'no_show' => $no_show,
                                'day_use' => $day_use,
                                'notes' => $notes,
                                'total' => $total_amount,
                                'total_taxes_and_services' => $total_amount_tax,
                                'avgPrice' => $total_amount,
                                'political' => [
                                    'rate' => [
                                        'name' => $rate["rate"]["calendarys"][0]["policies_rates"]["name"] ?? 'N/A',
                                        //                                'message' => "@@@".$rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'],
                                        'message' => (isset($rate["rate"]["calendarys"][0]["policies_rates"]["translations"]) ? ($rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'] ?? '') : ''),
                                        'example' => $rate,
                                    ],
                                    'cancellation' => [
                                        "name" => $message,
                                        "details" => $cancellation_details,
                                    ],
                                ],
                                'taxes_and_services' => $rate["taxes_and_services"],
                                'supplements' => $rate["supplements"],
                                'rate' => [
                                    'quantity_adults' => $rate["quantity_adults"],
                                    'quantity_child' => $rate["quantity_child"],
                                    'quantity_extras' => $rate["quantity_extras"],
                                    'total_amount' => $total_amount,
                                    'total_amount_adult' => $rate["total_amount_adult"],
                                    'total_amount_child' => $rate["total_amount_child"],
                                    'total_amount_infants' => $rate["total_amount_infants"],
                                    'total_amount_extras' => $rate["total_amount_extras"],
                                    'people_coverage' => $rate["people_coverage"],
                                    'quantity_inventory_taken' => $rate["quantity_inventory_taken"],
                                    'amount_days' => $rates_calendars,
                                ],
                            ];
                        }
                        //agregar habitacion al arreglo
                        $best_options["rooms"][] = [
                            'room_id' => $opcion["id"],
                            'room_type_id' => $room["room_type_id"],
                            'room_type' => $opcion['room_type']['translations'][0]['value'] ?? 'N/A',
                            'name' => $room_name,
                            'description' => $room_description,
                            'gallery' => $room_gallery,
                            'max_capacity' => $opcion["max_capacity"],
                            'max_adults' => $opcion["max_adults"],
                            'max_child' => $opcion["max_child"],
                            'quantity_adults' => $opcion["quantity_adults"],
                            'quantity_child' => $opcion["quantity_child"],
                            'quantity_infants' => $opcion["quantity_infants"],
                            'quantity_rooms' => $opcion["quantity_rooms"],
                            'total_amount' => number_format($opcion["total_amount"], 2, '.', ''),
                            'rates' => $rates,
                        ];
                    }
                }
            }

            $roomsConfirmed = collect($rooms)
                ->filter(function ($room, $key) {
                    return $room['best_price'] > 0 && $room['rates'][0]['onRequest'] === 1;
                })
                ->sortBy('best_price')
                ->values();

            $roomsOnRequest = collect($rooms)
                ->filter(function ($room, $key) {
                    return $room['best_price'] > 0 && $room['rates'][0]['onRequest'] === 0;
                })
                ->sortBy('best_price')
                ->values();


            $roomsZero = collect($rooms)
                ->filter(function ($room, $key) {
                    return $room['best_price'] == 0;
                })
                ->sortBy(function ($room, $key) {
                    return $room['rates'][0]['onRequest'] ? 0 : 1;
                })
                ->values();

            $rooms = $roomsConfirmed->merge($roomsOnRequest)->merge($roomsZero);

            $princeHotel = 0;
            if (count($best_options) > 0) {
                if ($best_options["total_rate_amount"] > 0) {
                    $princeHotel = $best_options["total_rate_amount"];
                } else {
                    if (count($rooms) > 0) {
                        if (count($rooms[0]['rates']) > 0) {
                            $princeHotel = $rooms[0]['rates'][0]['total'];
                        }
                    }
                }
            } else {
                if (count($rooms) > 0) {
                    if (count($rooms[0]['rates']) > 0) {
                        $princeHotel = $rooms[0]['rates'][0]['total'];
                    }
                }
            }

            $code_ = ChannelHotel::where("hotel_id", $hotel_client["hotel"]["id"])->where("channel_id", 1)->first();

            $country_name = '';
            $country_iso = '';
            $state_name = '';
            $state_iso = '';
            $city_name = '';
            $zone_name = '';
            $district_name = '';
            $hoteltype_name = '';

            if (!empty($hotel_client["hotel"]["country"]) and count($hotel_client["hotel"]["country"]["translations"] ?? []) > 0) {
                $country_name = $hotel_client["hotel"]["country"]["translations"][0]["value"] ?? '';
                $country_iso = $hotel_client["hotel"]["country"]["iso"] ?? '';
            }

            if (!empty($hotel_client["hotel"]["state"]) and count($hotel_client["hotel"]["state"]["translations"] ?? []) > 0) {
                $state_name = $hotel_client["hotel"]["state"]["translations"][0]["value"] ?? '';
                $state_iso = $hotel_client["hotel"]["state"]["iso"] ?? '';
            }

            if (!empty($hotel_client["hotel"]["city"]) and count($hotel_client["hotel"]["city"]["translations"] ?? []) > 0) {
                $city_name = $hotel_client["hotel"]["city"]["translations"][0]["value"] ?? '';
            }

            if (!empty($hotel_client["hotel"]["zone"]) and count($hotel_client["hotel"]["zone"]["translations"] ?? []) > 0) {
                $zone_name = $hotel_client["hotel"]["zone"]["translations"][0]["value"] ?? '';
            }

            if (!empty($hotel_client["hotel"]["district"]) and count($hotel_client["hotel"]["district"]["translations"] ?? []) > 0) {
                $district_name = $hotel_client["hotel"]["district"]["translations"][0]["value"] ?? '';
            }

            if (!empty($hotel_client["hotel"]["hoteltype"]) and count($hotel_client["hotel"]["hoteltype"]["translations"] ?? []) > 0) {
                $hoteltype_name = $hotel_client["hotel"]["hoteltype"]["translations"][0]["value"] ?? '';
            }

            $hoteltypeclass = $this->getHotelTypeClass($hotel_client["hotel"]["hoteltypeclass"]);
            $typeclass_name = $hoteltypeclass['name'];
            $typeclass_color = $hoteltypeclass['color'];
            $typeclass_order = $hoteltypeclass['order'];


            if ($priceRange) {
                if ($princeHotel < $priceRange['min'] || $princeHotel > $priceRange['max']) {
                    continue;
                }
            }

            $hotels[0]["city"]["hotels"][] = [
                "id" => $hotel_client["hotel"]["id"],
                "code" => $code_ ? $code_->code : $hotel_client["hotel"]["id"],
                "flag_new" => $hotel_client["hotel"]["flag_new"],
                "date_end_flag_new" => $hotel_client["hotel"]["date_end_flag_new"],
                "name" => $hotel_client["hotel"]["name"],
                "country" => $country_name,
                "country_iso" => $country_iso,
                "state" => $state_name,
                "state_iso" => $state_iso,
                "city" => $city_name,
                "district" => $district_name,
                "zone" => $zone_name,
                "description" => $hotel_description,
                "address" => $hotel_address,
                "summary" => $hotel_summary,
                // "notes" => (isset($hotel_client["hotel"]["notes"])) ? $hotel_client["hotel"]["notes"] : '',
                "notes" => $hotel_notes ? $hotel_notes : '',
                "chain" => $hotel_client["hotel"]["chain"]["name"],
                "logo" => $hotel_logo,
                "category" => (int)($hotel_client["hotel"]["stars"]),
                "type" => $hoteltype_name,
                "class" => $typeclass_name,
                "hoteltypeclass" => $hotel_client["hotel"]["hoteltypeclass"],
                "color_class" => $typeclass_color, //$hotel_client["hotel"]["typeclass"]["color"],
                "order_class" => $typeclass_order,
                "price" => $princeHotel,
                "coordinates" => [
                    'latitude' => $hotel_client["hotel"]["latitude"],
                    'longitude' => $hotel_client["hotel"]["longitude"],
                ],
                "popularity" => (count($hotel_client["hotel"]["hotelpreferentials"] ?? []) > 0 && isset($hotel_client["hotel"]["hotelpreferentials"][0])) ? $hotel_client["hotel"]["hotelpreferentials"][0]['value'] : 0,  //$hotel_client["hotel"]["preferential"],
                "favorite" => $this->checkHotelFavorite($hotel_client["hotel"]["id"]),
                "checkIn" => $hotel_client["hotel"]["check_in_time"],
                "checkOut" => $hotel_client["hotel"]["check_out_time"],
                "political_children" => [
                    "child" => [
                        "allows_child" => $hotel_client["hotel"]["allows_child"],
                        "min_age_child" => $hotel_client["hotel"]["min_age_child"],
                        "max_age_child" => $hotel_client["hotel"]["max_age_child"],
                    ],
                    "infant" => [
                        "allows_teenagers" => $hotel_client["hotel"]["allows_teenagers"],
                        "min_age_teenagers" => $hotel_client["hotel"]["min_age_teenagers"],
                        "max_age_teenagers" => $hotel_client["hotel"]["max_age_teenagers"],
                    ],
                ],
                "amenities" => $amenities,
                "galleries" => $hotel_gallery,
                "best_options" => $best_options,
                "rooms" => $rooms,
                "best_option_taken" => false,
                "best_option_cart_items_id" => [],
                'flag_migrate' => $flat_migrate,
            ];
        }

        $hotels[0]["city"]["min_price_search"] = number_format($min_price_search, 2, '.', '');
        $hotels[0]["city"]["max_price_search"] = number_format($max_price_search, 2, '.', '');
        $hotels[0]["city"]["quantity_hotels"] = count($hotels[0]["city"]["hotels"]);

        $token_search_frontend = $faker->unique()->uuid;
        $hotels[0]["city"]["token_search_frontend"] = $token_search_frontend;

        $this->storeTokenSearchHotels($token_search_frontend, $hotels, $this->expiration_search_hotels);

        foreach ($hotels[0]['city']['hotels'] as $key => $hotel) {
            $best_options = [];

            if (isset($hotel['best_options']['rooms']) and !empty($hotel['best_options']['rooms'])) {
                $rooms = [];
                foreach ($hotel['best_options']['rooms'] as $key_room => $room) {
                    $images = $this->searchGalleryCloudinary('room', $room['room_id']);
                    $rooms[$key_room] = $room;
                    $rooms[$key_room]['gallery'] = $images;
                }

                $best_options = [
                    'rooms' => $rooms
                ];
            }

            $hotels[0]['city']['hotels'][$key]['best_options'] = $best_options;

            $rooms = [];

            foreach ($hotel['rooms'] as $key_room => $room) {
                $images = $this->searchGalleryCloudinary('room', $room['room_id']);

                $rooms[$key_room] = $room;
                $rooms[$key_room]['gallery'] = $images;
            }

            $hotels[0]['city']['hotels'][$key]['rooms'] = $rooms;
            $hotels[0]['city']['hotels'][$key]['galleries'] = $this->searchGalleryCloudinary('hotel', $hotel['id']);
        }

        $response = [
            'success' => true,
            'data' => $hotels,
            'expiration_token' => $this->expiration_search_hotels,
        ];

        Cache::put('response_', $response, 3600);

        return $response;
        // } catch (Exception $e) {
        //     $statusCode = $e->getCode()>0 ? $e->getCode() : 500;
        //     return [
        //         'success' => false,
        //         'status_code' => $statusCode,
        //         'error' => $e->getMessage()
        //     ];
        // }
    }

}
