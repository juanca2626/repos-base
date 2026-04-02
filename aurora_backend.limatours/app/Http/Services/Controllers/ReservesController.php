<?php


namespace App\Http\Services\Controllers;


use App\City;
use App\ClientSeller;
use App\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Services\Traits\ClientHotelUtilTrait;
use App\Http\Traits\Currencies;
use App\Http\Traits\Hotels;
use App\Http\Traits\Services;
use App\Language;
use App\PackagePlanRateCategory;
use App\RatesPlansRooms;
use App\Reservation;
use App\Room;
use App\RoomType;
use App\State;
use App\Http\Traits\CalculateCancellationlPolicies;
use App\Http\Traits\Images;
use App\Http\Traits\Package;
use App\Http\Traits\Reservations;
use App\Zone;
use App\ChannelHotel;
use App\ClientRatePlan;
use App\RatesPlans;
use App\RatesPlansPromotions;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReservesController extends Controller
{
    use Package, Reservations, Services, Images, Currencies, CalculateCancellationlPolicies, Hotels, ClientHotelUtilTrait;

    public $expiration_search_services = 1440;// 24 horas
    public $expiration_search_hotels = 180;// 3 horas

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserve(Request $request)
    {
        $client_id = $this->getClientId($request->input('client_id'));

        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $reference = $request->input('reference');

        //        $hotels = $request->input('hotels'); // Supuestamente va a recibir aparte como otro producto
//        $services = $request->input('services'); // Supuestamente va a recibir aparte como otro producto
        $packages = $request->input('packages');

        $response = [];

        if ($packages) {

            $package_name = $reference;
            $n = 0;
            foreach ($packages as $package) {
                if ($n > 0) {
                    break;
                }
                $n++;
                $quantity_rooms_sgl = $package['quantity_rooms_sgl'];
                $quantity_rooms_dbl = $package['quantity_rooms_dbl'];
                $quantity_rooms_tpl = $package['quantity_rooms_tpl'];
                $date = $package['date'];
                $package_plan_rate_category_id = $package['package_plan_rate_category_id'];
                //cantidad real de pax
                $quantity_adults = $package["quantity_persons"][0]["adults"];
                $quantity_child = $package["quantity_persons"][0]["child"];

                // "executive_id" => 2827, //Todo Revisar executive_id (VALIDAR) 2827->Paul //

                $data_package = PackagePlanRateCategory::where('id', $package_plan_rate_category_id)
                    ->with(['plan_rate.package'])
                    ->first();
                $object_id = $data_package->plan_rate->package->id;

                $reservation_data = [
                    "client_id" => $client_id,
                    "file_code" => "",
                    "reference" => $package_name,
                    "reference_new" => $reference,
                    "guests" => [
                        [
                            "given_name" => "Pasajero 1",
                            "surname" => "Pasajero 1"
                        ]
                    ],
                    "reservations" => [],
                    "reservations_services" => [],
                    "date_init" => $date,
                    "entity" => 'Package',
                    "object_id" => $object_id
                ];

                $services = $this->getServicesByPackage($package_plan_rate_category_id, $lang);
                $array_services_new = $this->updateDateInServices($services, $date, false);
                $services = $array_services_new["services"];
                $services = $this->getHotelsWithStatus($services, $date);
                //                $itinerary = $this->getItineraryByService($services);
                foreach ($services as $service) {
                    try {
                        if ($service["type"] == "hotel") {

                            foreach ($service["service_rooms"] as $index => $service_room) {
                                $rate_plan_room_model = RatesPlansRooms::find($service_room["rate_plan_room_id"]);
                                $room_model = Room::find($rate_plan_room_model->room_id);
                                $room_type = RoomType::find($room_model->room_type_id);

                                $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                            }
                            //                            return response()->json($service["service_rooms"][0]["rate_plan_room_id"]);
                            try {
                                $hotels_id = [$service["object_id"]];
                                $hotel_id = $service["object_id"];

                                $check_in = $service["date_in"];
                                $check_out = $service["date_out"];

                                $destinationsRequest = Request::createFrom($request);

                                $destinationsRequest->request->add([
                                    'client_id' => $client_id,
                                    'hotels_id' => $hotels_id,
                                ]);

                                $destinations = $this->destinations($destinationsRequest);

                                $hotelsRequest = Request::createFrom($request);

                                $hotelsRequest->request->add([
                                    'client_id' => $client_id,
                                    'hotels_id' => $hotels_id,
                                    'destiny' => [
                                        'code' => $destinations->getData()[0]->code,
                                        'label' => $destinations->getData()[0]->label
                                    ],
                                    'date_from' => $check_in,
                                    'date_to' => $check_out,
                                    'typeclass_id' => 'all',
                                    'quantity_rooms' => 1,
                                    'quantity_persons_rooms' => [
                                        [
                                            "room" => 1,
                                            "adults" => 1,
                                            "child" => 0,
                                            "ages_child" => [
                                                [
                                                    'child' => 1,
                                                    'age' => 1,
                                                ],
                                            ],
                                        ],
                                    ],
                                ]);

                                $hotel_search = $this->hotels($hotelsRequest);

                                $result_hotel = $hotel_search->getData()->data[0]->city;
                                $token_search = $result_hotel->token_search;


                                foreach ($result_hotel->hotels as $hotel_ind => $hotel) {
                                    if ($hotel->id != $hotel_id) {
                                        continue;
                                    }

                                    $room_request = Request::createFrom($request); //$request->toArray();

                                    $rooms_selected = [];
                                    //Reserva para Simple
                                    foreach ($service["service_rooms"] as $service_room) {
                                        if ($service_room["occupation"] == 1) {
                                            if ($quantity_rooms_sgl > 0) {
                                                foreach ($hotel->rooms as $room_ind => $room) {
                                                    //return response()->json($room,200);
                                                    foreach ($room->rates as $rate) {
                                                        if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                            //return response()->json($room->rates,200);
                                                            $room_request->request->replace([
                                                                'token_search' => $token_search,
                                                                'hotel_id' => $hotel_id,
                                                                'room_id' => $room->room_id,
                                                                'rate_id' => $rate->rateId,
                                                                'rate_plan_id' => $rate->ratePlanId,
                                                                'date_from' => $check_in,
                                                                'date_to' => $check_out,
                                                                'client_id' => $client_id,
                                                                'rooms' => [
                                                                    [
                                                                        'quantity_adults' => 1,
                                                                        'quantity_child' => 0,
                                                                    ],
                                                                ],
                                                            ]);

                                                            for ($i = 0; $i < $quantity_rooms_sgl; $i++) {
                                                                array_push($reservation_data["reservations"], [
                                                                    "token_search" => $result_hotel->token_search,
                                                                    "room_ident" => 0,
                                                                    "hotel_id" => $hotel_id,
                                                                    "best_option" => false,
                                                                    "rate_plan_room_id" => $rate->rateId,
                                                                    "suplements" => [],
                                                                    "guest_note" => "",
                                                                    "date_from" => $check_in,
                                                                    "date_to" => $check_out,
                                                                    "quantity_adults" => $quantity_adults,
                                                                    "quantity_child" => $quantity_child,
                                                                    "child_ages" => []
                                                                ]);
                                                            }
                                                            break;
                                                        }

                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //Reserva para Doble
                                    foreach ($service["service_rooms"] as $service_room) {
                                        if ($service_room["occupation"] == 2) {
                                            if ($quantity_rooms_dbl > 0) {
                                                foreach ($hotel->rooms as $room_ind => $room) {
                                                    //return response()->json($room,200);
                                                    foreach ($room->rates as $rate) {
                                                        if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                            //return response()->json($room->rates,200);
                                                            $room_request->request->replace([
                                                                'token_search' => $token_search,
                                                                'hotel_id' => $hotel_id,
                                                                'room_id' => $room->room_id,
                                                                'rate_id' => $rate->rateId,
                                                                'rate_plan_id' => $rate->ratePlanId,
                                                                'date_from' => $check_in,
                                                                'date_to' => $check_out,
                                                                'client_id' => $client_id,
                                                                'rooms' => [
                                                                    [
                                                                        'quantity_adults' => 1,
                                                                        'quantity_child' => 0,
                                                                    ],
                                                                ],
                                                            ]);

                                                            for ($i = 0; $i < $quantity_rooms_dbl; $i++) {
                                                                array_push($reservation_data["reservations"], [
                                                                    "token_search" => $result_hotel->token_search,
                                                                    "room_ident" => 0,
                                                                    "hotel_id" => $hotel_id,
                                                                    "best_option" => false,
                                                                    "rate_plan_room_id" => $rate->rateId,
                                                                    "suplements" => [],
                                                                    "guest_note" => "",
                                                                    "date_from" => $check_in,
                                                                    "date_to" => $check_out,
                                                                    "quantity_adults" => $quantity_adults,
                                                                    "quantity_child" => $quantity_child,
                                                                    "child_ages" => []
                                                                ]);
                                                            }
                                                            break;
                                                        }

                                                    }
                                                }
                                            }

                                        }
                                    }
                                    //Reserva para Triple
                                    foreach ($service["service_rooms"] as $service_room) {
                                        if ($service_room["occupation"] == 3) {
                                            if ($quantity_rooms_tpl > 0) {
                                                foreach ($hotel->rooms as $room_ind => $room) {
                                                    //return response()->json($room,200);
                                                    foreach ($room->rates as $rate) {
                                                        if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                            //return response()->json($room->rates,200);
                                                            $room_request->request->replace([
                                                                'token_search' => $token_search,
                                                                'hotel_id' => $hotel_id,
                                                                'room_id' => $room->room_id,
                                                                'rate_id' => $rate->rateId,
                                                                'rate_plan_id' => $rate->ratePlanId,
                                                                'date_from' => $check_in,
                                                                'date_to' => $check_out,
                                                                'client_id' => $client_id,
                                                                'rooms' => [
                                                                    [
                                                                        'quantity_adults' => 3,
                                                                        'quantity_child' => 0,
                                                                    ],
                                                                ],
                                                            ]);
                                                            for ($i = 0; $i < $quantity_rooms_tpl; $i++) {
                                                                array_push($reservation_data["reservations"], [
                                                                    "token_search" => $result_hotel->token_search,
                                                                    "room_ident" => 0,
                                                                    "hotel_id" => $hotel_id,
                                                                    "best_option" => false,
                                                                    "rate_plan_room_id" => $rate->rateId,
                                                                    "suplements" => [],
                                                                    "guest_note" => "",
                                                                    "date_from" => $check_in,
                                                                    "date_to" => $check_out,
                                                                    "quantity_adults" => $quantity_adults,
                                                                    "quantity_child" => $quantity_child,
                                                                    "child_ages" => []
                                                                ]);
                                                            }
                                                            break;
                                                        }

                                                    }
                                                }
                                            }

                                        }
                                    }
                                }


                            } catch (\Exception $e) {
                                $response = ['success' => false, 'error' => $e->getMessage()];
                            }
                        }
                        if ($service["type"] == "service") {
                            $services_id = [$service["object_id"]];
                            $service_id = $service["object_id"];
                            $check_in = $service["date_in"];

                            $destinationsRequest = Request::createFrom($request);


                            $destinationsRequest->request->add([
                                'client_id' => $client_id,
                                'services_id' => $services_id,
                            ]);
                            // 2231 - 2308
//                            if( $service["object_id"] === 2231 ){
////                                return Response::json( $destinationsRequest ); die;
//                                return $client_id; die;
//                            }
                            $destinations = $this->destinations_services($destinationsRequest);

                            $servicesRequest = ServiceRequest::createFrom($request);

                            $servicesRequest->request->add([
                                'client_id' => (int) $client_id,
                                'origin' => [
                                    'code' => $destinations->getData()->data->origins[0]->code,
                                    'label' => $destinations->getData()->data->origins[0]->label
                                ],
                                'destiny' => [
                                    'code' => $destinations->getData()->data->destinations[0]->code,
                                    'label' => $destinations->getData()->data->destinations[0]->label
                                ],
                                'lang' => 'es',
                                'date' => $check_in,
                                'services_id' => $services_id,
                                'quantity_persons' => [
                                    "adults" => (int) $quantity_adults,
                                    "child" => (int) $quantity_child,
                                    "ages_child" => [
                                        [
                                            'child' => 0,
                                            'age' => 0,
                                        ],
                                    ],
                                ],
                            ]);

                            $service_search = $this->services($servicesRequest);

                            $result_service = $service_search->getData()->data;
                            $token_search = $result_service->token_search;
                            array_push($reservation_data["reservations_services"], [
                                'token_search' => $token_search,
                                'service_ident' => 0,
                                'service_id' => $service_id,
                                'rate_plan_id' => $result_service->services[0]->rate->id,
                                'reservation_time' => empty($result_service->services[0]->reservation_time) ? '00:00' : $result_service->services[0]->reservation_time,
                                'date_from' => $check_in,
                                "guest_note" => "",
                                'quantity_adults' => $quantity_adults,
                                'quantity_child' => $quantity_child,
                                'child_ages' => []
                            ]);
                        }
                    } catch (\Exception $e) {
                        return Response::json([
                            'success' => false,
                            //                            'message' => $result_service,
                            'message' => 'object_id: ' . $service["object_id"] . ' - The Line:' . $e->getLine(),
                        ]);
                    }
                }

                $reservationRequest = $request->merge($reservation_data);
                //                return $reservationRequest;
                $response = $this->reservationPush($reservationRequest);
            }
        }

        //        return response()->json($reservation_data, 200);
        return Response::json($response);

    }

    public function services(ServiceRequest $request)
    {

        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        //id ó ids de servicios
        $service_id = $request->post('services_id');
        if (!isset($service_id)) {
            $service_id = null;
        }


        $limit = $request->post('limit');
        if (isset($limit)) {
            if ($request->post('limit') <= 50) {
                $limit = $request->input('limit');
            }
        } else {
            $limit = 50;
        }

        $recommendations = $request->post('recommendations');


        //idioma
        $lang = ($request->has('lang')) ? $request->post('lang') : 'en';
        $language = Language::where('iso', $lang)->first();
        if ($language->count() > 0 and isset($lang)) {
            $language_id = $language->id;
        } else {
            $lang = 'es';
            $language_id = 1;
        }

        //origen - destino
        $origin = $request->post('origin');

        if (!isset($origin)) {
            $origin = null;
        }

        $destiny = $request->post('destiny');
        if (!isset($destiny)) {
            $destiny = null;
        }

        //fecha
        $date_to = $request->post('date');
        if (!isset($date_to)) {
            $date_to = Carbon::now()->format('Y-m-d H:m');
        }

        $category = $request->post('category');
        if (!isset($category)) {
            $category = null;
        }

        $classification = $request->post('classification');
        if (!isset($classification)) {
            $classification = null;
        }

        $experience = $request->post('experience');
        if (!isset($experience)) {
            $experience = null;
        }

        $filter = $request->post('filter');
        if (!isset($filter)) {
            $filter = null;
        }

        $quantity_persons = $request->post('quantity_persons');
        if (!isset($quantity_persons)) {
            $quantity_persons = [
                'adults' => 2,
                'child' => 0,
                'age_childs' => [
                    'age' => 0
                ],
            ];
        }

        $type = $request->post('type'); // compoartido / privado / ninguno
        if (!isset($type)) {
            $type = null;
        }

        $currency_to_change = Currency::whereIso('USD')->first();
        $exchange_rate = 1;
        $symbol_to_change = '';
        $iso_to_change = '';
        if ($currency_to_change) {
            $exchange_rate = (double) $currency_to_change->exchange_rate;
            $symbol_to_change = $currency_to_change->symbol;
            $iso_to_change = $currency_to_change->iso;
        }
        $quantity_adults = $quantity_persons['adults'];
        $quantity_child = $quantity_persons['child'];
        $quantity_total_pax = $quantity_adults + $quantity_child;

        //TODO Parametros de busqueda
        $search_parameters = [
            'origin' => ($origin == null) ? '' : $origin,
            'destiny' => ($destiny == null) ? '' : $destiny,
            'date' => $date_to,
            'type' => $type,
            'category' => $category,
            'classification' => $classification,
            'experience' => $experience,
            'filter_by_name' => $filter,
            'quantity_persons' => $quantity_persons
        ];

        $origin_country_id = "";
        $origin_state_id = "";
        $origin_city_id = "";
        $origin_zone_id = "";
        $destiny_country_id = "";
        $destiny_state_id = "";
        $destiny_city_id = "";
        $destiny_zone_id = "";

        if ($origin !== null) {
            $origin_codes = explode(",", $origin['code']);
            for ($i = 0; $i < count($origin_codes); $i++) {
                if ($i == 0) {
                    $origin_country_id = $origin_codes[$i];
                }
                if ($i == 1) {
                    $origin_state_id = $origin_codes[$i];
                }
                if ($i == 2) {
                    $origin_city_id = $origin_codes[$i];
                }
                if ($i == 3) {
                    $origin_zone_id = $origin_codes[$i];
                }
            }
        }

        if ($destiny !== null) {
            $destiny_codes = explode(",", $destiny['code']);
            for ($i = 0; $i < count($destiny_codes); $i++) {
                if ($i == 0) {
                    $destiny_country_id = $destiny_codes[$i];
                }
                if ($i == 1) {
                    $destiny_state_id = $destiny_codes[$i];
                }
                if ($i == 2) {
                    $destiny_city_id = $destiny_codes[$i];
                }
                if ($i == 3) {
                    $destiny_zone_id = $destiny_codes[$i];
                }
            }
        }

        $child_min_age = 0;
        $accept_child = false;

        //logica que verifica si en la busqueda tienen niños
        if ($quantity_child > 0) {
            $accept_child = true;
            foreach ($quantity_persons["age_childs"] as $child) {
                if ($child_min_age === 0) {
                    $child_min_age = (int) $child["age"];
                }
                if ($child["age"] < $child_min_age) {
                    $child_min_age = (int) $child["age"];
                }
            }
        }


        $this->setClient($client_id);
        $to = Carbon::parse($date_to);
        $period = $to->year;
        if (!$accept_child) {
            $child_min_age = null;
        }
        $current_date = Carbon::now('America/Lima')->format('Y-m-d H:i:s');
        $current_date = Carbon::createFromFormat('Y-m-d H:i:s', (string) $current_date, 'America/Lima');

        $time = Carbon::now('America/Lima')->format('H:i:00');
        $date_to_time = $date_to . ' ' . $time;
        $check_in = Carbon::createFromFormat('Y-m-d H:i:s', (string) $date_to_time, 'America/Lima');
        $dayOfWeek = strtolower($to->englishDayOfWeek);
        $services_collect = $this->getServicesClient(
            $service_id,
            $current_date,
            $to,
            $origin_country_id,
            $destiny_country_id,
            $origin_state_id,
            $origin_city_id,
            $origin_zone_id,
            $destiny_state_id,
            $destiny_city_id,
            $destiny_zone_id,
            $filter,
            $category,
            $classification,
            $experience,
            $type,
            $period,
            $lang,
            $language_id,
            $quantity_total_pax,
            $child_min_age,
            $check_in,
            $recommendations,
            $dayOfWeek
        );


        $services_collect = $services_collect->paginate($limit);

        $services_client = $services_collect->toArray();
        //        return $services_client;
        $markup_general = DB::table('markups')->where('client_id', $client_id)->where(
            'period',
            Carbon::parse($date_to)->year
        )->first();
        $services = [];
        $min_price_search = 0;
        $max_price_search = 0;
        $textSearch = "#";
        $faker = Faker::create();
        $token_search = $faker->unique()->uuid;
        foreach ($services_client['data'] as $index_service => $service_client) {
            try {
                $services[$index_service]['id'] = $service_client['id'];
                $services[$index_service]['name'] = $service_client['name'];
                $services[$index_service]['code'] = $service_client['aurora_code'];
                $services[$index_service]['coordinates'] = [
                    'latitude' => $service_client['latitude'],
                    'longitude' => $service_client['longitude'],
                ];
                $services[$index_service]['reserve_from_days'] = $service_client['qty_reserve'];
                $services[$index_service]['equivalence'] = $service_client['equivalence_aurora'];
                $services[$index_service]['affected_igv'] = $service_client['affected_igv'];
                $services[$index_service]['affected_markup'] = $service_client['affected_markup'];
                $services[$index_service]['allows_guide'] = $service_client['allow_guide'];
                $allows_child = 0;
                $children_ages = [
                    'min' => 0,
                    'max' => 0
                ];
                if ($service_client['allow_child'] and count($service_client['children_ages']) > 0) {
                    $allows_child = 1;
                    $children_ages = [
                        'min' => $service_client['children_ages'][0]['min_age'],
                        'max' => $service_client['children_ages'][0]['max_age']
                    ];
                }
                $services[$index_service]['allows_child'] = $allows_child;
                $services[$index_service]['children_age_allowed'] = $children_ages;
                $services[$index_service]['allows_infant'] = $service_client['allow_infant'];
                $services[$index_service]['confirmation_hours_limit'] = $service_client['limit_confirm_hours'];
                $services[$index_service]['include_accommodation'] = $service_client['include_accommodation'];
                $services[$index_service]['unit_of_duration'] = $service_client['unit_durations']['translations'][0]['value'];
                $services[$index_service]['tag'] = $service_client['tag_service'];
                $services[$index_service]['favorite'] = false;
                $services[$index_service]['languages_guide'] = $service_client["languages_guide"];
                $services[$index_service]['allowed_quantity'] = [
                    'min' => $service_client["pax_min"],
                    'max' => $service_client["pax_max"],
                ];


                //Todo Valoraciones
                $services[$index_service]['rated'] = (count($service_client['rated']) > 0) ? $service_client['rated'][0]['rated'] : 0;

                $symbol = $service_client['currency']['symbol'];
                $iso = $service_client['currency']['symbol'];
                if ($service_client['currency']['iso'] == 'PEN' & $symbol_to_change !== '') {
                    //Moneda
                    $symbol = $symbol_to_change;
                    $iso = $iso_to_change;
                }

                //Todo Modeda
                $services[$index_service]['currency'] = [
                    'symbol' => $symbol,
                    'iso' => $iso,
                ];

                //Todo Categoria
                $services[$index_service]['category'] = [
                    'category' => $service_client['service_sub_category']['service_categories']['translations'][0]['value'],
                    'sub_category' => $service_client['service_sub_category']['translations'][0]['value'],
                ];
                //Todo Duracion del servicio
                $services[$index_service]['duration'] = $service_client['duration'];
                $city_origin = (isset($service_client['service_origin'][0]['city']['translations'][0]['value'])) ? $service_client['service_origin'][0]['city']['translations'][0]['value'] : null;
                $zone_origin = (isset($service_client['service_origin'][0]['zone']['translations'][0]['value'])) ? $service_client['service_origin'][0]['zone']['translations'][0]['value'] : null;
                $services[$index_service]['origin'] = [
                    'origin_display' => '',
                    'country' => $service_client['service_origin'][0]['country']['translations'][0]['value'],
                    'state' => $service_client['service_origin'][0]['state']['translations'][0]['value'],
                    'city' => $city_origin,
                    'zone' => $zone_origin,
                ];
                $services[$index_service]['origin']['origin_display'] = implode(
                    ',',
                    array_filter($services[$index_service]['origin'])
                );
                $city_destiny = (isset($service_client['service_destination'][0]['city']['translations'][0]['value'])) ? $service_client['service_destination'][0]['city']['translations'][0]['value'] : null;
                $zone_destiny = (isset($service_client['service_destination'][0]['zone']['translations'][0]['value'])) ? $service_client['service_destination'][0]['zone']['translations'][0]['value'] : null;
                $services[$index_service]['destiny'] = [
                    'country' => $service_client['service_destination'][0]['country']['translations'][0]['value'],
                    'state' => $service_client['service_destination'][0]['state']['translations'][0]['value'],
                    'city' => $city_destiny,
                    'zone' => $zone_destiny,
                ];
                $services[$index_service]['destiny']['destiny_display'] = implode(
                    ',',
                    array_filter($services[$index_service]['destiny'])
                );

                //Todo tipo de servicio
                $services[$index_service]['service_type'] = [
                    'id' => $service_client['service_type']['id'],
                    'name' => $service_client['service_type']['translations'][0]['value'],
                    'code' => $service_client['service_type']['code'],
                ];

                //Todo clasificacion
                $image_classification = '';
                if (count($service_client['classification']['galeries']) > 0) {
                    $image_classification = $this->verifyCloudinaryImg(
                        $service_client['classification']['galeries'][0]['url'],
                        500,
                        450,
                        ''
                    );
                }
                $services[$index_service]['classification'] = [
                    'id' => $service_client['classification']['id'],
                    'name' => $service_client['classification']['translations'][0]['value'],
                    'image' => $image_classification
                ];

                $itinerary = [];
                $itinerary_commercial = [];
                if (!empty($service_client['service_translations'][0]['itinerary'])) {
                    $itinerary = $this->getFormatItinerary($service_client['service_translations'][0]['itinerary']);
                }

                if (!empty($service_client['service_translations'][0]['itinerary_commercial'])) {
                    $itinerary_commercial = $this->getFormatItinerary($service_client['service_translations'][0]['itinerary_commercial']);
                }


                //Todo Descripcion
//                $services[$index_service]['descriptions'] = [
//                    'name_commercial' => $service_client['service_translations'][0]['name'],
//                    'description' => $service_client['service_translations'][0]['description'],
//                    'itinerary' => $itinerary,
//                    'summary' => $service_client['service_translations'][0]['summary'],
//                ];

                //Todo descripcion comercial
                $services[$index_service]['descriptions'] = [
                    'name_commercial' => $service_client['service_translations'][0]['name_commercial'],
                    'description' => $service_client['service_translations'][0]['description_commercial'],
                    'itinerary' => $itinerary_commercial,
                    'summary' => $service_client['service_translations'][0]['summary_commercial'],
                ];

                $seservicesrvices[$index_service]['languages_guide'] = [
                    'language_display' => '',
                    'iso_display' => '',
                    'languages' => []
                ];

                if (count($service_client['languages_guide']) > 0) {
                    $languages = [];
                    foreach ($service_client['languages_guide'] as $language) {
                        $languages[] = [
                            'id' => $language['language']['id'],
                            'name' => $language['language']['name'],
                            'iso' => $language['language']['iso'],
                        ];
                    }
                    $services[$index_service]['languages_guide'] = [
                        'language_display' => implode(",", array_column($languages, 'name')),
                        'iso_display' => implode(",", array_column($languages, 'iso')),
                        'languages' => $languages
                    ];
                }

                $services[$index_service]['experiences'] = [];
                //Todo Experiencias
                foreach ($service_client['experience'] as $experience) {
                    $services[$index_service]['experiences'][] = [
                        'id' => $experience['id'],
                        'name' => $experience['translations'][0]['value'],
                        'color' => $experience['color'],
                    ];
                }

                $services[$index_service]['restrictions'] = [];
                //Todo restricciones
                foreach ($service_client['restriction'] as $restriction) {
                    $services[$index_service]['restrictions'][] = [
                        'id' => $restriction['id'],
                        'name' => $restriction['translations'][0]['value'],
                    ];
                }

                $services[$index_service]['galleries'] = [];
                //Todo Galerias
                foreach ($service_client['galleries'] as $gallery) {
                    $services[$index_service]['galleries'][] = [
                        'url' => $this->verifyCloudinaryImg($gallery['url'], 400, 450, '')
                    ];
                }

                // Verifico si tiene el markup por tarifa
                if (count($service_client['service_rate']) > 0 and count($service_client['service_rate'][0]['markup_rate_plan']) > 0) {
                    $markup = $service_client['service_rate'][0]['markup_rate_plan'][0]['markup'];
                } elseif (count($service_client['markup_service']) > 0) { //Verifico si tiene el markup por servicio
                    $markup = $service_client['markup_service'][0]['markup'];
                } else { //Si no tomo el markup general de servicios
                    $markup = $markup_general->service;
                }

                //Todo highlights
                $services[$index_service]['highlights'] = [];
                foreach ($service_client['highlights'] as $key => $item) {
                    $highlight = $item['featured']['translations'][0]['value'];
                    $services[$index_service]['highlights'][] = [
                        'highlight' => $highlight
                    ];
                }

                //Todo instructions
                $services[$index_service]['instructions'] = [];
                foreach ($service_client['instructions'] as $key => $item) {
                    $instruction = $item['instructions']['translations'][0]['value'];
                    $services[$index_service]['instructions'][] = [
                        'instruction' => $instruction
                    ];
                }

                //Todo instructions
                $services[$index_service]['physical_intensity'] = [];
                if (!empty($service_client['physical_intensity'])) {
                    $services[$index_service]['physical_intensity'] = [
                        'name' => $service_client['physical_intensity']['translations'][0]['value'],
                        'color' => $service_client['physical_intensity']['color'],
                    ];
                }

                $services[$index_service]['operations']['details'] = [];
                //Todo Detalle dia a dia de las operaciones
                $key = 0;

                foreach ($service_client['operability'] as $keyA => $itemA) {
                    $start_time = $itemA['start_time'];
                    $services[$index_service]['operations']['details'][$key] = [
                        'day' => $itemA['day'],
                        'departure_time' => $itemA['start_time'],
                        'shifts_available' => $itemA['shifts_available'],
                        'detail' => [],
                    ];
                    foreach ($itemA['services_operation_activities'] as $keyB => $itemB) {
                        if ($keyB > 0) {
                            $count = count($services[$index_service]['operations']['details'][$key]['detail']);
                            $start_time = $services[$index_service]['operations']['details'][$key]['detail'][$count - 1]['end_time'];
                        }
                        $start_end = Carbon::createFromFormat(
                            'H:i:s',
                            $start_time
                        )->addMinutes($itemB['minutes'])->toTimeString();
                        $services[$index_service]['operations']['details'][$key]['detail'][] = [
                            'detail' => (count($itemB['service_type_activities']['translations']) == 0) ? '' : $itemB['service_type_activities']['translations'][0]['value'],
                            'start_time' => $start_time,
                            'end_time' => $start_end,
                        ];
                    }
                    $key++;
                }

                //Todo Dias de operacion y horarios
                $services[$index_service]['operations']['days'] = [
                    'monday' => $service_client['service_rate'][0]['service_rate_plans'][0]['monday'],
                    'tuesday' => $service_client['service_rate'][0]['service_rate_plans'][0]['tuesday'],
                    'wednesday' => $service_client['service_rate'][0]['service_rate_plans'][0]['wednesday'],
                    'thursday' => $service_client['service_rate'][0]['service_rate_plans'][0]['thursday'],
                    'friday' => $service_client['service_rate'][0]['service_rate_plans'][0]['friday'],
                    'saturday' => $service_client['service_rate'][0]['service_rate_plans'][0]['saturday'],
                    'sunday' => $service_client['service_rate'][0]['service_rate_plans'][0]['sunday'],
                ];

                $services[$index_service]['operations']['schedule'] = [];
                //Todo Horario de opearcion
                $schedules = $service_client['schedules'];

                if (count($schedules) == 0) {
                    $service_start_time = Carbon::createFromFormat('H:i:s', '00:00:00', 'America/Lima');
                } else {
                    $time_week = $schedules[0]['services_schedule_detail'][0][strtolower($dayOfWeek)];
                    $time = (is_null($time_week)) ? '00:00:00' : $time_week;
                    if (Carbon::parse($check_in)->format('Y-m-d') == Carbon::parse($current_date)->format('Y-m-d')) {
                        $service_start_time = Carbon::createFromFormat('H:i:s', $time, 'America/Lima');
                    } else {
                        $check_in = Carbon::createFromFormat(
                            'Y-m-d H:i:s',
                            $date_to . ' ' . '00:00:00',
                            'America/Lima'
                        );
                        $service_start_time = Carbon::createFromFormat(
                            'Y-m-d H:i:s',
                            $date_to . ' ' . $time,
                            'America/Lima'
                        );
                    }
                }


                foreach ($schedules as $schedule) {
                    $monday = $schedule['services_schedule_detail'][0]['monday'] . ' - ' . $schedule['services_schedule_detail'][1]['monday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['monday'] === 0) {
                        $monday = null;
                    }
                    $tuesday = $schedule['services_schedule_detail'][0]['tuesday'] . ' - ' . $schedule['services_schedule_detail'][1]['tuesday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['tuesday'] === 0) {
                        $tuesday = null;
                    }
                    $wednesday = $schedule['services_schedule_detail'][0]['wednesday'] . ' - ' . $schedule['services_schedule_detail'][1]['wednesday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['wednesday'] === 0) {
                        $wednesday = null;
                    }
                    $thursday = $schedule['services_schedule_detail'][0]['thursday'] . ' - ' . $schedule['services_schedule_detail'][1]['thursday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['thursday'] === 0) {
                        $thursday = null;
                    }
                    $friday = $schedule['services_schedule_detail'][0]['friday'] . ' - ' . $schedule['services_schedule_detail'][1]['friday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['friday'] === 0) {
                        $friday = null;
                    }
                    $saturday = $schedule['services_schedule_detail'][0]['saturday'] . ' - ' . $schedule['services_schedule_detail'][1]['saturday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['saturday'] === 0) {
                        $saturday = null;
                    }
                    $sunday = $schedule['services_schedule_detail'][0]['sunday'] . ' - ' . $schedule['services_schedule_detail'][1]['sunday'];
                    if ($service_client['service_rate'][0]['service_rate_plans'][0]['sunday'] === 0) {
                        $sunday = null;
                    }
                    $services[$index_service]['operations']['schedule'][] = [
                        'monday' => $monday,
                        'tuesday' => $tuesday,
                        'wednesday' => $wednesday,
                        'thursday' => $thursday,
                        'friday' => $friday,
                        'saturday' => $saturday,
                        'sunday' => $sunday
                    ];
                }


                if ($service_client['service_type']['id'] === 1) {
                    $week_name = strtolower($to->format('l'));
                    $reserve_time = (count($schedules) > 0) ? $schedules[0]['services_schedule_detail'][0][$week_name] : Carbon::now()->format('H:m');
                    $services[$index_service]['reservation_time'] = $reserve_time;
                } elseif ($service_client['service_type']['id'] === 2) {
                    $services[$index_service]['reservation_time'] = '';
                } else {
                    $services[$index_service]['reservation_time'] = Carbon::now()->format('H:m');
                }

                //Todo Inclusiones
                $inclusions = $service_client['inclusions'];
                foreach ($inclusions as $inclusion) {
                    //Todo incluye

                    if ($inclusion['include']) {
                        $services[$index_service]['inclusions'][$inclusion['day']]['include'][] = [
                            'day' => $inclusion['day'],
                            'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                        ];
                    }

                    //Todo no incluye
                    if (!$inclusion['include']) {
                        $services[$index_service]['inclusions'][$inclusion['day']]['no_include'][] = [
                            'day' => $inclusion['day'],
                            'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                        ];
                    }

                }

                if (count($service_client['inclusions']) > 0) {
                    $services[$index_service]['inclusions'] = array_values($services[$index_service]['inclusions']);
                } else {
                    $services[$index_service]['inclusions'] = [];
                }

                $total_base = 0;
                $price_per_person_adult = 0;
                $price_per_person_child = 0;
                $pax_amounts = $service_client['service_rate'][0]['service_rate_plans'];

                foreach ($pax_amounts as $pax_amount) {
                    if ($service_client['affected_markup'] == 1) {
                        $price_per_person_adult += ($pax_amount['price_adult'] * 1) + (($pax_amount['price_adult'] * 1) * ($markup / 100));
                        if ($allows_child and $quantity_child > 0) {
                            $price_per_person_child += ($pax_amount['price_child'] * 1) + (($pax_amount['price_child'] * 1) * ($markup / 100));
                        }
                    } else {
                        $price_per_person_adult += ($pax_amount['price_adult'] * 1);
                        if ($allows_child and $quantity_child > 0) {
                            $price_per_person_child += ($pax_amount['price_child'] * 1);
                        }
                    }

                }

                $offer_value = 0;
                $total_base = (roundLito($price_per_person_adult) * $quantity_adults) + roundLito($price_per_person_child) * $quantity_child;

                if (count($service_client['service_rate'][0]['offers']) > 0) {
                    $value = $service_client['service_rate'][0]['offers'][0]['value'];
                    $offer_value = $value;
                    if ($service_client['service_rate'][0]['offers'][0]['is_offer']) {
                        $services[$index_service]['offer'] = true;
                        $price_per_person_adult = $price_per_person_adult - ($price_per_person_adult * ($value / 100));
                        $price_per_person_child = $price_per_person_child - ($price_per_person_child * ($value / 100));
                    } else {
                        $services[$index_service]['offer'] = false;
                        $price_per_person_adult = $price_per_person_adult + ($price_per_person_adult * ($value / 100));
                        $price_per_person_child = $price_per_person_child + ($price_per_person_child * ($value / 100));
                    }
                } else {
                    $services[$index_service]['offer'] = false;
                }


                $services[$index_service]['offer_value'] = $offer_value;
                $price_per_person = roundLito($price_per_person_adult) * 1;
                $price_per_adult = (float) roundLito($price_per_person_adult);
                $price_per_child = (float) roundLito($price_per_person_child);
                $price_adult = $price_per_adult * $quantity_adults;
                $price_child = $price_per_child * $quantity_child;
                $total_amount_calculated = $price_adult + $price_child;

                if ($price_adult == null) {
                    $price_adult = (float) 0;
                }

                if ($price_child == null) {
                    $price_child = (float) 0;
                }

                //Todo Conversion de moneda soles a dolares
                if ($service_client['currency']['iso'] == 'PEN' & $symbol_to_change !== '') {
                    $total_amount = $this->convert_currency($total_amount_calculated, $exchange_rate);
                    $price_adult = $this->convert_currency($price_adult, $exchange_rate);
                    $price_child = $this->convert_currency($price_child, $exchange_rate);
                } else {
                    $total_amount = $total_amount_calculated;
                }

                //Todo Obtenemos la politica y su penalidad
                $service_rate_plans = [];
                foreach ($service_client['service_rate'][0]['service_rate_plans'] as $rate_plan) {
                    $details_params_cancelations = [];
                    foreach ($rate_plan['policy']['parameters'] as $item) {
                        $details_params_cancelations[] = [
                            'id' => $item['id'],
                            'unit_duration' => $item['unit_duration'],
                            'min_num' => $rate_plan['policy']['min_num'],
                            'max_num' => $rate_plan['policy']['max_num'],
                            'to' => $item['max_hour'],
                            'from' => $item['min_hour'],
                            'tax' => $item['tax'],
                            'amount' => $item['amount'],
                            'service' => $item['service'],
                            'penalty_id' => $item['penalty']['id'],
                            'penalty_name' => $item['penalty']['name'],
                        ];
                    }

                    $political_penalties = $this->calculateCancellationPoliciesServices(
                        $current_date,
                        $check_in,
                        $total_amount,
                        collect($details_params_cancelations),
                        $quantity_total_pax,
                        $service_start_time
                    );

                    $politicals = [
                        'id' => $rate_plan['policy']['id'],
                        'name' => $rate_plan['policy']['name'],
                        'cancellation' => [
                            'parameters' => $details_params_cancelations,
                            'penalties' => $political_penalties['penalties']
                        ]
                    ];

                    $service_rate_plans[] = [
                        'id' => $rate_plan['id'],
                        'service_cancellation_policy_id' => $rate_plan['service_cancellation_policy_id'],
                        'date_from' => $rate_plan['date_from'],
                        'date_to' => $rate_plan['date_to'],
                        'pax_from' => $rate_plan['pax_from'],
                        'pax_to' => $rate_plan['pax_to'],
                        'price_adult' => $rate_plan['price_adult'],
                        'price_child' => $rate_plan['price_child'],
                        'price_infant' => $rate_plan['price_infant'],
                        'price_guide' => $rate_plan['price_guide'],
                        'political' => $politicals,
                    ];
                }
                $on_request = 1;
                $inventory_id = null;
                //Todo verificamos si tiene inventario si no tiene lo ponemos en RQ
                if (isset($service_client['service_rate'][0]['inventory']) and count($service_client['service_rate'][0]['inventory']) > 0) {
                    $on_request = 0;
                    $inventory_id = $service_client['service_rate'][0]['inventory'][0]['id'];
                }

                //Todo Armamos la tarifa
                $services[$index_service]['rate'] = [
                    'id' => $service_client['service_rate'][0]['id'],
                    'name' => $service_client['service_rate'][0]['translations'][0]['value'],
                    'markup' => (double) $markup,
                    'rate_plans' => $service_rate_plans,
                    'inventory_id' => $inventory_id,
                ];

                $total_taxes = 0;
                $sub_total = $total_amount;

                if ($service_client['affected_igv'] == 1 and count($service_client['tax']) > 0) {
                    $tax = (double) $service_client['tax'][0]['amount'];

                    $total_taxes = ($total_amount * ($tax / 100));
                    $total = $total_amount + $total_taxes;
                    $total_amount = $total;
                }

                if (Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 3) {
                    $services[$index_service]['notes'] = $service_client['notes'];
                }


                $services[$index_service]['price_per_person'] = $price_per_person;
                $services[$index_service]['price_per_adult'] = $price_per_adult;
                $services[$index_service]['price_per_child'] = $price_per_child;
                $services[$index_service]['quantity_adult'] = $quantity_adults;
                $services[$index_service]['quantity_child'] = $quantity_child;
                $services[$index_service]['total_amount_adult'] = $price_adult;
                $services[$index_service]['total_amount_child'] = $price_child;
                $services[$index_service]['affected_igv'] = $service_client['affected_igv'];
                $services[$index_service]['sub_total'] = $sub_total;
                $services[$index_service]['total_taxes'] = $total_taxes;
                $services[$index_service]['total_amount'] = $total_amount;
                $services[$index_service]['total_base_amount'] = $total_base;
                $services[$index_service]['base_pax'] = ($quantity_adults + $quantity_child);
                $services[$index_service]['cart_items_id'] = '';
                $services[$index_service]['taken'] = false;
                $services[$index_service]['date_reserve'] = $date_to;
                $services[$index_service]['on_request'] = $on_request;
                $services[$index_service]['token_search'] = $token_search;
                //Todo Sacamos el precio minimo y maximo
                if ($index_service === 0) {
                    $min_price_search = $total_amount;
                    $max_price_search = $total_amount;
                } else {
                    //minimo
                    if ($total_amount < $min_price_search) {
                        $min_price_search = $total_amount;
                    }
                    //maximo
                    if ($total_amount > $max_price_search) {
                        $max_price_search = $total_amount;
                    }
                }
            } catch (\Exception $e) {
                return Response::json([
                    'data' => [],
                    'success' => false,
                    'message' => $service_client['aurora_code'] . ' - ' . $e->getLine(),
                    'expiration_token' => ''
                ]);
            }
        }

        $data = [
            "token_search" => $token_search,
            'search_parameters' => $search_parameters,
            'services' => $services,
            'current_page' => $services_client['current_page'],
            'first_page_url' => $services_client['first_page_url'],
            'from' => $services_client['from'],
            'last_page' => $services_client['last_page'],
            'last_page_url' => $services_client['last_page_url'],
            'next_page_url' => $services_client['next_page_url'],
            'path' => $services_client['path'],
            'per_page' => $services_client['per_page'],
            'prev_page_url' => $services_client['prev_page_url'],
            'to' => $services_client['to'],
            'quantity_services' => $services_client['total'],
        ];

        $this->storeTokenSearchHotels($token_search, $services, $this->expiration_search_services);

        return Response::json([
            'data' => $data,
            'success' => true,
            'expiration_token' => $this->expiration_search_services
        ]);
    }

    public function destinations(Request $request)
    {
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->get('client_id');
        }

        if ($request->get('hotels_id')) {
            //$client_id = 1;
            $hotels_client = $this->getClientHotels(
                $client_id,
                Carbon::now('America/Lima')->year,
                $request->get('hotels_id')
            );
        } else {
            //$client_id = 1;
            $hotels_client = $this->getClientHotels($client_id, Carbon::now('America/Lima')->year);
        }

        $destinations_country_state = $this->checkCountryState($hotels_client);

        $destinations_country_state_city = $this->checkCountryStateCity($hotels_client);

        $destinations_country_state_city_district = $this->checkCountryStateCityDistrict($hotels_client);

        $destinations = array_merge($destinations_country_state, $destinations_country_state_city);

        $destinations = array_merge($destinations, $destinations_country_state_city_district);

        $destination_select = [];

        foreach ($destinations as $destination) {
            array_push($destination_select, [
                "code" => $destination["ids"],
                "label" => $destination["description"],
            ]);
        }
        sort($destination_select);

        return Response::json($destination_select);
    }

    public function hotels(Request $request)
    {
        //Todo Obtenemos el Id del cliente
        $client_id = $this->getClientId($request);

        $hotels_id = $request->get('hotels_id');
        if (!isset($hotels_id)) {
            $hotels_id = [];
        }

        $rate_plan_room_search = $request->get('rate_plan_room_search', []);
        $allow_children = $request->get('allow_children', false);
        $hotels_search_code = $request->get('hotels_search_code');
        if (is_array($hotels_search_code)) {
            $hotels_id = ChannelHotel::where('channel_id', 1)->whereIn('code', $hotels_search_code)->pluck('hotel_id');
            $hotels_search_code = '';
        } else {
            if (!isset($hotels_search_code)) {
                $hotels_search_code = '';
            }
        }

        $destiny = $request->get('destiny');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $typeclass = $request->get('typeclass_id');
        $setMarkup = $request->get('set_markup');
        $zeroRates = $request->get('zero_rates', false);
        if (!isset($setMarkup)) {
            $setMarkup = 0;
        }
        $quantity_rooms = (int) $request->get('quantity_rooms');
        $quantity_persons_rooms = $request->get('quantity_persons_rooms', []);
        $promotional_rate = (int) @$request->get('promotional_rate');
        if ($request->has('lang') and !empty($request->get('lang'))) {
            $lang_iso = $request->get('lang');
        } else {
            $lang_iso = 'en';
        }
        \App::setLocale($lang_iso);
        $language = Language::where('iso', $lang_iso)->first();
        $language_id = $language->id;
        $search_parameters = [
            'client_id' => $client_id,
            'destiny' => $destiny,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'typeclass_id' => $typeclass,
            'quantity_persons_rooms' => $quantity_persons_rooms,
            'promotional_rate' => $promotional_rate
        ];

        $destiny_codes = explode(",", $destiny["code"]);
        $country_id = "";
        $state_id = "";
        $city_id = "";
        $district_id = "";

        //separar codigos de destino
        for ($i = 0; $i < count($destiny_codes); $i++) {
            if ($i == 0) {
                $country_id = $destiny_codes[$i];
            }
            if ($i == 1) {
                $state_id = $destiny_codes[$i];
            }
            if ($i == 2) {
                $city_id = $destiny_codes[$i];
            }
            if ($i == 3) {
                $district_id = $destiny_codes[$i];
            }
        }

        $typeclass_id = "";
        //Asignar si existe filtro por clase de hotel
        if ($typeclass != "all" && $typeclass != "hotel_id") {
            $typeclass_id = $typeclass;
        }

        $quantityPersons = $this->getQuantityPersonsRoom($quantity_persons_rooms, $allow_children);

        $total_people_adults = $quantityPersons->get('total_people_adults');
        $total_people_child = $quantityPersons->get('total_people_child');
        $total_people_search = $quantityPersons->get('total_people_search');

        $accept_child = $quantityPersons->get('accept_child');
        //        $children_ages = $quantityPersons->get('children_ages');
        $child_min_age = $quantityPersons->get('child_min_age');
        //        $child_max_age = $quantityPersons->get('child_max_age');

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);

        $this->setClient($client_id);
        $period = $from->year;

        if ($accept_child) {
            //Logica para verificar si los hoteles aceptan niños
            $hotels_client_hotel_id_list = $this->getClientHotelsIds(
                $country_id,
                $state_id,
                $city_id,
                $district_id,
                $typeclass_id,
                $period,
                $hotels_id,
                $hotels_search_code,
                !!$child_min_age
            );
        } else {
            $hotels_client_hotel_id_list = $this->getClientHotelsIds(
                $country_id,
                $state_id,
                $city_id,
                $district_id,
                $typeclass_id,
                $period,
                $hotels_id,
                $hotels_search_code
            );
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
            $rate_plans_ids_ignore = ClientRatePlan::where('client_id', $client_id)->where('period', $period)
                ->pluck('rate_plan_id')->toArray();

            $rate_plans_available = RatesPlans::whereIn('hotel_id', $hotels_client_hotel_id_list)
                ->where('status', 1);

            if ($promotional_rate == 1) {
                $rate_plans_available = $rate_plans_available->where('promotions', 1);
            }

            $rate_plans_available = $rate_plans_available->whereNotIn('id', $rate_plans_ids_ignore)
                ->pluck('id')->toArray();

            $rooms_ids_ignore = Room::whereIn('hotel_id', $hotels_client_hotel_id_list)->where('state', 0)
                ->pluck('id')->toArray();

            //            $rate_plan_room_ids_include = RatesPlansRooms::
//            whereIn('rates_plans_id', $rate_plans_available)
//                ->whereNotIn('room_id', $rooms_ids_ignore)
//                ->where('status', 1);
//
//            if(count($rate_plan_room_ids_include)) {
//                $rate_plan_room_ids_include = $rate_plan_room_ids_include
//                    ->whereIn('id', $rate_plan_room_search);
//            }
//            $rate_plan_room_ids_include = $rate_plan_room_ids_include->pluck('id');


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

            //Esto es para tarifas de Channels validando tiempo de estadia y dias de anticipacion de reserva
            $rate_plan_room_channels_time_stay_and_days_advance_reservation = $this->getArrayIdsRatesPlansRoomsChannelsTimeStayAndDaysAdvanceReservation(
                $hotels_client_hotel_id_list,
                $from,
                $days_advance_reservation,
                $reservation_days,
                $rate_plan_room_search
            );

            if (count($rate_plan_room_channels_time_stay_and_days_advance_reservation) > 0) {
                //Validacion de tarifas de channel para ver si tiene un importe para el numero de adultos de busqueda o tiene un importe total por habitacion
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
                        array_push($rate_plan_room_ids_include, $rate_channel);
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

            /*foreach ($quantity_persons_rooms as $index => $quantity_person_room) {
                //Calculo  y Seleccion para adultos y niños
                if ($quantity_person_room["child"] > 0) {
                    if ($quantity_person_room["adults"] >= 1) {
                        //buscar si hay una habitacion con la misma capacidad o mayor de la cantidad de personas que esta buscando
                        foreach ($hotel_client['hotel']['rooms'] as $index_room => $room) {
                            $ocupacion_habitacion = $room["max_capacity"];
                            // Cantidad igual a la cantidad de personas que esta buscando
                            if ($ocupacion_habitacion >= ($quantity_person_room["adults"] + $quantity_person_room["child"])) {
                                $room['tarifas_seleccionadas'] = [];
                                $tarifas_seleccionadas = [];

                                $room["quantity_adults"] = 0;
                                $room["quantity_child"] = 0;
                                $room["quantity_infants"] = 0;
                                $room["quantity_rooms"] = 0;
                                $room["total_amount"] = 0;

                                foreach ($room["rates_plan_room"] as $rates_plan_room_index => $rates_plan_room) {
                                    if ($rates_plan_room["channel_id"] == 1) {
//                                        $ocupacion_tarifa = $rates_plan_room["calendarys"][0]["policies_rates"]["max_occupancy"];
                                        $ocupacion_tarifa = $room['room_type']['occupation'];
                                    } else {
                                        $ocupacion_tarifa = $rates_plan_room["calendarys"][0]["max_occupancy"];
                                        if ($ocupacion_tarifa == null) {
                                            $ocupacion_tarifa = $ocupacion_habitacion;
                                        }
                                    }

                                    //check exist price child
                                    $check_exist_price_child = false;
                                    $quantity_check_price_child = 0;
                                    for ($j = 0; $j < count($rates_plan_room["calendarys"]); $j++) {
                                        if ($rates_plan_room["calendarys"][$j]["rate"][0]["price_child"] == 0 || $rates_plan_room["calendarys"][$j]["rate"][0]["price_child"] > 0) {
                                            $quantity_check_price_child++;
                                        }
                                    }
                                    if ($quantity_check_price_child == count($rates_plan_room["calendarys"])) {
                                        $check_exist_price_child = true;
                                    }

                                    //check exist price extra
                                    $check_exist_price_extra = false;
                                    $quantity_check_price_extra = 0;
                                    for ($j = 0; $j < count($rates_plan_room["calendarys"]); $j++) {
                                        if ($rates_plan_room["calendarys"][$j]["rate"][0]["price_extra"] > 0 || $rates_plan_room["calendarys"][$j]["rate"][0]["price_total"] > 0) {
                                            $quantity_check_price_extra++;
                                        }
                                    }
                                    if ($quantity_check_price_extra == count($rates_plan_room["calendarys"])) {
                                        $check_exist_price_extra = true;
                                    }
                                    //TODO crear una validacion antes que debe restringir la cantidad de personas por lo permitido en la habitacion
                                    $quantity_persons = $this->getQuantityPersons($quantity_person_room["adults"],
                                        $quantity_person_room["child"], $ocupacion_habitacion, $ocupacion_tarifa,
                                        $room["min_adults"], $room["max_adults"], $room["max_child"],
                                        $quantity_person_room["ages_child"], $hotel_client['hotel']['min_age_child'],
                                        $hotel_client['hotel']['max_age_child']);

                                    if ($check_exist_price_child) {
                                        if (($quantity_persons["quantity_extras"] > 0 && $check_exist_price_extra) || $quantity_persons["quantity_extras"] == 0) {
                                            $policies_cancellation = [];
                                            if ($rates_plan_room["channel_id"] == 1) {
                                                $rates_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons($this->getMarkupFromsearch($hotel_client['client_markup'],
                                                    $hotel_client['hotel']['markup'], $rates_plan_room),
                                                    $quantity_persons["quantity_adults"],
                                                    $quantity_persons["quantity_child"],
                                                    $quantity_persons["quantity_extras"]);
                                            } else {
                                                $rates_plan_room_new = $this->calculateRatePlanRoomChannelsCalendarByPersons($this->getMarkupFromsearch($hotel_client['client_markup'],
                                                    $hotel_client['hotel']['markup'], $rates_plan_room),
                                                    $rates_plan_room["id"],
                                                    $room["max_capacity"],
                                                    $quantity_persons["quantity_adults"],
                                                    $quantity_persons["quantity_child"],
                                                    $quantity_persons["quantity_extras"]);

                                            }

                                            if (!$rates_plan_room_new) {
                                                continue;
                                            }

                                            $rate = [
                                                'total_amount' => $rates_plan_room_new["total_amount"],
                                                'total_amount_adult' => $rates_plan_room_new["total_amount_adult"],
                                                'total_amount_child' => $rates_plan_room_new["total_amount_child"],
                                                'total_amount_infants' => 0,
                                                'total_amount_extras' => $rates_plan_room_new["total_amount_extra"],
//                                                'quantity_adults' => $quantity_person_room["adults"],
//                                                'quantity_child' => $quantity_person_room["child"],
                                                'quantity_adults' => $quantity_persons["quantity_adults"],
                                                'quantity_child' => $quantity_persons["quantity_child"],
                                                'quantity_infants' => 0,
                                                'quantity_extras' => $quantity_persons["quantity_extras"],
                                                'ages_child' => $quantity_person_room["ages_child"],
                                                'people_coverage' => $quantity_person_room["adults"] + $quantity_person_room["child"],
                                                'quantity_inventory_taken' => 1,
                                                'policy_cancellation' => [],
                                                'policies_cancellation' => [],
                                                'taxes_and_services' => [],
                                                'supplements' => [],
                                                'rate' => $rates_plan_room_new,
                                            ];

                                            $room["quantity_adults"] += $quantity_person_room["adults"];
                                            $room["quantity_child"] += $quantity_person_room["child"];
                                            $room["quantity_infants"] = 0;
                                            $room["quantity_rooms"] += 1;
                                            $room["total_amount"] += $rates_plan_room_new["total_amount"];

                                            $hotels_client[$index_hotel]['best_options']['quantity_rooms'] += 1;
                                            $hotels_client[$index_hotel]['best_options']['quantity_adults'] += $quantity_person_room["adults"];
                                            $hotels_client[$index_hotel]['best_options']['quantity_child'] += $quantity_person_room["child"];

                                            $hotels_client[$index_hotel]['best_options']['total_sub_rate_amount'] += $rates_plan_room_new["total_amount"];
                                            $hotels_client[$index_hotel]['best_options']['total_rate_amount'] += $rate["total_amount"];
                                            array_push($room['tarifas_seleccionadas'], $rate);
                                            array_push($hotels_client[$index_hotel]['best_options']['rooms'], $room);
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    //Calculo  y Seleccion para solo adultos

                    //buscar si hay una habitacion con la misma capacidad de la cantidad de personas que esta buscando
                    $rooms_ = [];
                    foreach ($hotel_client['hotel']['rooms'] as $index_room => $room) {

                        $occupation_room = $room['room_type']['occupation'];

                        if ($occupation_room == $quantity_person_room["adults"]) {

                            //Find room best
                            foreach ($room["rates_plan_room"] as $rates_plan_room_index => $rates_plan_room) {

                                $min_inventory = 0;

                                if (count($rates_plan_room["inventories"]) == 0 && $rates_plan_room["bag"] == 1) {

                                    $rates_plan_room["inventories"] = $rates_plan_room["bag_rate"]["bag_room"]["inventory_bags"];
                                }
                                if (isset($rates_plan_room["inventories"])) {
                                    for ($i = 0; $i < count($rates_plan_room["inventories"]); $i++) {
                                        if ($i === 0) {
                                            $min_inventory = $rates_plan_room["inventories"][0]["inventory_num"] - $rates_plan_room["inventories"][0]["total_booking"];
                                        } else {
                                            if ($rates_plan_room["inventories"][$i]["inventory_num"] < $min_inventory) {
                                                $min_inventory = $rates_plan_room["inventories"][$i]["inventory_num"] - $rates_plan_room["inventories"][$i]["total_booking"];
                                            }
                                        }
                                    }
                                } else {
                                    $rates_plan_room[$rates_plan_room_index]["inventories"] = [];
                                }
                                if ($min_inventory > 0) {
                                    if ($room["bed_additional"] == 1) {
                                        array_push($rooms_, [
                                            "index_room" => $index_room,
                                            "index_rate_plan_room" => $rates_plan_room_index,
                                            "amount" => $rates_plan_room["calendarys"][0]["rate"][0]["price_adult"] + $rates_plan_room["calendarys"][0]["rate"][0]["price_extra"],
                                            "inventory" => $min_inventory
                                        ]);

                                    } else {
                                        array_push($rooms_, [
                                            "index_room" => $index_room,
                                            "index_rate_plan_room" => $rates_plan_room_index,
                                            "amount" => $rates_plan_room["calendarys"][0]["rate"][0]["price_adult"],
                                            "inventory" => $min_inventory
                                        ]);
                                    }

                                }
                            }
                        }
                    }
                    $best_option_room = null;
                    if (count($rooms_) > 0) {
                        $best_option_room = $rooms_[0];
                    }

                    foreach ($rooms_ as $index_room => $room) {
                        if ($room["amount"] < $best_option_room["amount"]) {
                            $best_option_room = $room;
                        }
                    }
                    if ($best_option_room != null) {
                        foreach ($hotel_client['hotel']['rooms'] as $index_room => $room) {

                            $room['tarifas_seleccionadas'] = [];
                            $tarifas_seleccionadas = [];
                            $ocupacion_habitacion = $room["max_capacity"];

                            $occupation_room = $room['room_type']['occupation'];
                            $room["quantity_adults"] = 0;
                            $room["quantity_child"] = 0;
                            $room["quantity_infants"] = 0;
                            $room["quantity_rooms"] = 0;
                            $room["total_amount"] = 0;
                            if ($index_room == $best_option_room["index_room"]) {
                                foreach ($room["rates_plan_room"] as $rates_plan_room_index => $rates_plan_room) {
                                    if ($rates_plan_room_index == $best_option_room["index_rate_plan_room"]) {
                                        $policies_cancellation = [];

                                        if ($rates_plan_room["channel_id"] == 1) {
                                            if ($room["bed_additional"] == 1) {
                                                $rates_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons($this->getMarkupFromsearch($hotel_client['client_markup'],
                                                    $hotel_client['hotel']['markup'], $rates_plan_room),
                                                    $quantity_person_room["adults"], 0, 1);
                                            } else {
                                                $rates_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons($this->getMarkupFromsearch($hotel_client['client_markup'],
                                                    $hotel_client['hotel']['markup'], $rates_plan_room),
                                                    $quantity_person_room["adults"], 0, 0);
                                            }

                                        } else {
                                            $rates_plan_room_new = $this->calculateRatePlanRoomChannelsCalendarByPersons($this->getMarkupFromsearch($hotel_client['client_markup'],
                                                $hotel_client['hotel']['markup'], $rates_plan_room),
                                                $rates_plan_room["id"], $room["max_capacity"],
                                                $quantity_person_room["adults"], 0,
                                                0);
                                        }

                                        if ($rates_plan_room_new) {
                                            if ($room["bed_additional"] == 1) {
                                                $rate = [
                                                    'total_amount' => $rates_plan_room_new["total_amount"],
                                                    'total_amount_adult' => $rates_plan_room_new["total_amount_adult"],
                                                    'total_amount_child' => 0,
                                                    'total_amount_infants' => 0,
                                                    'total_amount_extras' => $rates_plan_room_new["total_amount_extra"],
                                                    'quantity_adults' => $quantity_person_room["adults"],
                                                    'quantity_child' => 0,
                                                    'quantity_infants' => 0,
                                                    'quantity_extras' => 1,
                                                    'ages_child' => [],
                                                    'people_coverage' => $quantity_person_room["adults"],
                                                    'quantity_inventory_taken' => 1,
                                                    'policy_cancellation' => [],
                                                    'policies_cancellation' => [],
                                                    'taxes_and_services' => [],
                                                    'supplements' => [],
                                                    'rate' => $rates_plan_room_new,
                                                ];

                                            } else {
                                                $rate = [
                                                    'total_amount' => $rates_plan_room_new["total_amount"],
                                                    'total_amount_adult' => $rates_plan_room_new["total_amount_adult"],
                                                    'total_amount_child' => 0,
                                                    'total_amount_infants' => 0,
                                                    'total_amount_extras' => $rates_plan_room_new["total_amount_extra"],
                                                    'quantity_adults' => $quantity_person_room["adults"],
                                                    'quantity_child' => 0,
                                                    'quantity_infants' => 0,
                                                    'quantity_extras' => 0,
                                                    'ages_child' => [],
                                                    'people_coverage' => $quantity_person_room["adults"],
                                                    'quantity_inventory_taken' => 1,
                                                    'policy_cancellation' => [],
                                                    'policies_cancellation' => [],
                                                    'taxes_and_services' => [],
                                                    'supplements' => [],
                                                    'rate' => $rates_plan_room_new,
                                                ];
                                            }


                                            $room["quantity_adults"] += $quantity_person_room["adults"];
                                            $room["quantity_child"] = 0;
                                            $room["quantity_infants"] = 0;
                                            $room["quantity_rooms"] += 1;
                                            $room["total_amount"] += $rates_plan_room_new["total_amount"];

                                            $hotels_client[$index_hotel]['best_options']['quantity_rooms'] += 1;
                                            $hotels_client[$index_hotel]['best_options']['quantity_adults'] += $quantity_person_room["adults"];

                                            $hotels_client[$index_hotel]['best_options']['total_sub_rate_amount'] += $rates_plan_room_new["total_amount"];
                                            $hotels_client[$index_hotel]['best_options']['total_rate_amount'] += $rate["total_amount"];

                                            array_push($room['tarifas_seleccionadas'], $rate);
                                            array_push($hotels_client[$index_hotel]['best_options']['rooms'], $room);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }*/
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

                    $taxes_and_services = $this->addHotelExtraFees(
                        $applicable_fees,
                        $tarifas_seleccionada['rate']["rate_plan"],
                        $tarifas_seleccionada["total_amount"]
                    );

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

                    $policy_cancellation_calculated = $this->calculateCancellationlPolicies(
                        $current_date,
                        $check_in,
                        $check_out,
                        $tarifas_seleccionada["total_amount"],
                        $selected_policies_cancelation,
                        $guest_quantity,
                        $rooms_quantity
                    );

                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['policy_cancellation'] = $policy_cancellation_calculated['next_penalty'];
                    $hotels_client[$hotelInd]['best_options']['rooms'][$roomInd]['tarifas_seleccionadas'][$tarInd]['policies_cancellation'] = $policy_cancellation_calculated['penalties'];
                }
            }
        }

        //Agregar las tarifas On Request
        $rates_on_request = [];
        if (isset($rates_plan_rooms_on_request)) {
            $rates_on_request = RatesPlansRooms::whereIn('id', $rates_plan_rooms_on_request)
                ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                    return $query->whereHas('rate_plan', function ($query) use ($hotels_id) {
                        $query->whereIn('hotel_id', $hotels_id);
                    });
                })
                ->with([
                    //                'policies_cancelation' => function ($query) use ($from, $to, $period, $rates_plan_room) {
                    'policies_cancelation' => function ($query) {
                        $query->where('type', 'cancellations');
                        $query->with([
                            'policy_cancellation_parameter' => function ($query) {
                                $query->where('min_day', '>=', 0);
                                $query->where('max_day', '<>', 0);
                                $query->with('penalty');
                            },
                        ]);
                    },
                ])
                ->with([
                    'descriptions' => function ($query) {

                    }
                ])
                ->with([
                    'calendarys' => function ($query) use ($from, $to, $language_id) {
                        $query->where('date', '>=', $from);
                        $query->where('date', '<=', $to);
                        $query->orderBy('date');
                        $query->with([
                            'policies_rates' => function ($query) use ($language_id) {
                                $query->with([
                                    'policies_cancelation' => function ($query) {
                                        //                                        $query->with('policy_cancellation_parameter');
                                        $query->where('type', 'cancellations');
                                        $query->with([
                                            'policy_cancellation_parameter' => function ($query) {
                                                $query->where('min_day', '>=', 0);
                                                $query->where('max_day', '<>', 0);
                                                $query->with('penalty');
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with([
                                    'translations' => function ($query) use ($language_id) {
                                        $query->where('type', 'rate_policies');
                                        $query->where('language_id', $language_id);
                                    },
                                ]);
                            },
                        ]);
                        $query->with('rate');
                    },
                ])->with('channel')
                ->with('rate_plan.meal.translations')
                ->with([
                    'rate_plan' => function ($query) use ($period, $language_id) {
                        $query->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with([
                            'translations_no_show' => function ($query) use ($language_id) {
                                $query->where('slug', 'no_show');
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with([
                            'translations_day_use' => function ($query) use ($language_id) {
                                $query->where('slug', 'day_use');
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with([
                            'translations_notes' => function ($query) use ($language_id) {
                                $query->where('slug', 'notes');
                                $query->where('language_id', $language_id);
                            }
                        ]);
                        $query->with([
                            'meal.translations',
                            'markup' => function ($query) use ($period) {
                                $query->where('period', '>=', $period);
                            },
                        ]);
                    },
                ])
                ->with([
                    'inventories' => function ($query) use ($from, $to) {
                        $query->where('date', '>=', $from);
                        $query->where('date', '<=', $to);
                    },
                ])
                ->with([
                    'room' => function ($query) {
                        $query->with([
                            'galeries' => function ($query) {
                                $query->select('object_id', 'url');
                                $query->where('type', 'room');
                                $query->where('state', 1);
                            },
                        ]);

                        $query->with([
                            'channels' => function ($query) {
                                $query->wherePivot('state', '=', 1);
                                $query->wherePivot('code', '!=', '');
                                $query->wherePivot('code', '!=', 'null');
                            },
                        ]);

                        $query->with([
                            'room_type' => function ($query) {
                                $query->select('id', 'occupation');
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select('object_id', 'value');
                                        $query->where('type', 'roomtype');
                                        $query->where('language_id', 1);
                                    },
                                ]);
                            },
                        ]);

                        $query->with([
                            'translations' => function ($query) {
                                $query->select('object_id', 'value', 'slug');
                                $query->where('type', 'room');
                                $query->where('language_id', 1);
                            },
                        ]);
                    }
                ])->where('channel_id', 1)->get()->toArray();

        }


        // Obtengo un listado de hoteles que no esten incluidos en $hotels_client
        $hotels_id_onrequest = array();
        foreach ($rates_on_request as $index_rate => $rate) {
            $sw = false;
            foreach ($hotels_client as $index_hotel => $hotel_client) {
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
                $from, // agregado
                $typeclass_id // agregado
            );
            $merged = array_merge($hotels_client, $hotels_client_on_request);
            $hotels_client = $this->deepToArray($merged);
        }

        //validamos si hay habitaciones onRequest que no esten agregados a la matriz de los hoteles y las creamos pero si existen ya las agregamos
        foreach ($rates_on_request as $index_rate => $rate) {
            if ($rate["status"] == 1) {
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
                                    $hotels_client[$index_hotel]["hotel"]["rooms"][$index_room]["rates_plan_room"][] = $rate;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        // $this->getHotelAvailAmadeus();
        $faker = Faker::create();

        $token_search = $faker->unique()->uuid;

        if (env('SEARCH_HOTEL_TIME_LIVE')) {
            $this->expiration_search_hotels = env('SEARCH_HOTEL_TIME_LIVE');
        }

        // print_r(json_encode($hotels_client));
        // die('');
        $this->storeTokenSearchHotels($token_search, $hotels_client, $this->expiration_search_hotels);
        //procedimiento para estructurar json con los datos necesarios
        $hotels = [
            [
                "city" => [
                    "token_search" => $token_search,
                    "token_search_frontend" => "",
                    "ids" => $destiny["code"],
                    "description" => $destiny["label"],
                    "class" => [],
                    "zones" => [],
                    "hotels" => [],
                    "search_parameters" => $search_parameters,
                    "quantity_hotels" => 0
                ],
            ],
        ];

        $min_price_search = 0;
        $max_price_search = 0;
        foreach ($hotels_client as $index => $hotel_client) {
            $hotel_description = "";
            $hotel_address = "";
            $hotel_logo = "";
            $hotel_summary = "";
            $amenities = [];
            $hotel_gallery = [];
            $rooms = [];

            $best_options = [];

            if (count($hotel_client["best_options"]) > 0) {
                $best_options = [
                    "quantity_rooms" => $hotel_client["best_options"]["quantity_rooms"],
                    "quantity_adults" => $hotel_client["best_options"]["quantity_adults"],
                    "quantity_child" => $hotel_client["best_options"]["quantity_child"],
                    "total_taxes_and_services_amount" => roundLito((float) number_format(
                        $hotel_client["best_options"]["total_taxes_and_services_amount"],
                        2,
                        '.',
                        ''
                    )),
                    "total_supplements_amount" => roundLito((float) number_format(
                        $hotel_client["best_options"]["total_supplements_amount"],
                        2,
                        '.',
                        ''
                    )),
                    "total_sub_rate_amount" => roundLito((float) number_format(
                        $hotel_client["best_options"]["total_sub_rate_amount"],
                        2,
                        '.',
                        ''
                    )),
                    "total_rate_amount" => roundLito((float) number_format(
                        $hotel_client["best_options"]["total_rate_amount"],
                        2,
                        '.',
                        ''
                    )),
                    "rooms" => [],
                ];
            }

            //Agregar Arreglo de Zonas de Hoteles a la Busqueda
            if ($city_id != "") {
                $zones = Zone::where('city_id', $city_id)->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language->id);
                    }
                ])->get();
                foreach ($zones as $zone) {
                    $find_zone = false;
                    if (count($hotels[0]["city"]["zones"]) == 0) {
                        array_push($hotels[0]["city"]["zones"], [
                            "zone_name" => $zone["translations"][0]["value"],
                            "status" => false,
                        ]);
                    } else {
                        foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                            if ($zone_new["zone_name"] == $zone["translations"][0]["value"]) {
                                $find_zone = true;
                            }
                        }
                        if (!$find_zone) {
                            array_push($hotels[0]["city"]["zones"], [
                                "zone_name" => $zone["translations"][0]["value"],
                                "status" => false,
                            ]);
                        }
                    }
                }
            } else {
                $state = State::where('iso', $state_id)->first();
                $cities = City::where('state_id', $state->id)->get();
                foreach ($cities as $city) {
                    $zones = Zone::where('city_id', $city["id"])->with([
                        'translations' => function ($query) use ($language) {
                            $query->where('language_id', $language->id);
                        }
                    ])->get();
                    foreach ($zones as $zone) {
                        $find_zone = false;
                        if (count($hotels[0]["city"]["zones"]) == 0) {
                            array_push($hotels[0]["city"]["zones"], [
                                "zone_name" => $zone["translations"][0]["value"],
                                "status" => false,
                            ]);
                        } else {
                            foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                                if ($zone_new["zone_name"] == $zone["translations"][0]["value"]) {
                                    $find_zone = true;
                                }
                            }
                            if (!$find_zone) {
                                array_push($hotels[0]["city"]["zones"], [
                                    "zone_name" => $zone["translations"][0]["value"],
                                    "status" => false,
                                ]);
                            }
                        }
                    }
                }
            }

            //Agregar Arreglo de Clases de Hoteles a la Busqueda
            if (count($hotels[0]["city"]["class"]) == 0) {
                array_push($hotels[0]["city"]["class"], [
                    "class_name" => $hotel_client["hotel"]["typeclass"]["translations"][0]["value"],
                    "status" => false,
                ]);
            } else {
                $find_class = false;
                foreach ($hotels[0]["city"]["class"] as $class) {
                    if ($class["class_name"] == $hotel_client["hotel"]["typeclass"]["translations"][0]["value"]) {
                        $find_class = true;
                    }
                }
                if (!$find_class) {
                    array_push($hotels[0]["city"]["class"], [
                        "class_name" => $hotel_client["hotel"]["typeclass"]["translations"][0]["value"],
                        "status" => false,
                    ]);
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

            //cargar traducciones
            foreach ($hotel_client["hotel"]["translations"] as $translation) {
                if ($translation["slug"] === "hotel_address") {
                    $hotel_address = $translation["value"];
                }
                if ($translation["slug"] === "hotel_description") {
                    $hotel_description = $translation["value"];
                }
                if ($translation["slug"] === "summary") {
                    $hotel_summary = $translation["value"];
                }
            }

            //cargar galeria de hotel
            foreach ($hotel_client["hotel"]["galeries"] as $image) {
                if ($image["slug"] === "hotel_logo") {
                    $hotel_logo = secure_url('/') . '/images/' . $image["url"];
                }
                if ($image["slug"] === "hotel_gallery") {
                    $find_cloudinary = strpos($image["url"], "cloudinary");
                    if (!$find_cloudinary) {
                        array_push($hotel_gallery, url('/') . '/images/' . $image["url"]);
                    } else {
                        array_push($hotel_gallery, $image["url"]);
                    }
                }
            }

            //cargar amenities de hotel
            if (count($hotel_client["hotel"]["amenity"]) > 0) {
                foreach ($hotel_client["hotel"]["amenity"] as $amenity) {
                    array_push(
                        $amenities,
                        [
                            "name" => $amenity["translations"][0]["value"],
                            "image" => count($amenity["galeries"]) > 0 ? secure_url('/') . '/images/' . $amenity["galeries"][0]["url"] : ''
                        ]
                    );
                }
            }

            // Filtar lo Texes and services que seran aplicadon al hotel segun si el cliente es local o extranjero
            $applicable_fees = $this->getHotelApplicableFees($this->client(), $hotel_client['hotel']);

            //cargar habitaciones de hotel
            foreach ($quantity_persons_rooms as $quantity_persons_room) {
                foreach ($hotel_client["hotel"]["rooms"] as $room) {
                    //                    if ($room['room_type']['occupation'] == $quantity_persons_room["adults"]) {
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
                        array_push($room_gallery, $image["url"]);
                    }
                    //cargar tarifas de habitacion
                    $ids_rates_channels_charged = [];
                    foreach ($room["rates_plan_room"] as $rate) {
                        //calculo de inventario disponible dado un rango de fechas
                        $min_inventory = 0;

                        if (count($rate["inventories"]) == 0 && $rate["bag"] == 1) {
                            if (isset($rate["bag_rate"])) {
                                $rate["inventories"] = $rate["bag_rate"]["bag_room"]["inventory_bags"];
                            }
                        }

                        foreach ($rate["inventories"] as $index => $inventory) {
                            if ($index === 0) {
                                $min_inventory = $inventory["inventory_num"];
                            } else {
                                if ($inventory["inventory_num"] < $min_inventory) {
                                    $min_inventory = $inventory["inventory_num"];
                                }
                            }
                        }

                        if ($rate["status"] == 0) {
                            $min_inventory = 10;
                        }

                        // print_r($rate);
                        // die('min' . $min_inventory);

                        //--------------------------------------------------calculo de importes para 1 adulto----------------------------------------------------------------

                        $policies_cancellation = [];
                        $check_other_step = true;

                        $markupFromsearch = $this->getMarkupFromsearch(
                            $hotel_client['client_markup'],
                            $hotel_client['hotel']['markup'],
                            $rate,
                            $setMarkup
                        );
                        if ($rate["channel_id"] == 1) {
                            $rates_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                                $markupFromsearch,
                                $quantity_persons_room["adults"],
                                $quantity_persons_room["child"],
                                0,
                                $quantity_persons_room["ages_child"],
                                $hotel_client['hotel'],
                                $room["bed_additional"],
                                $room
                            );
                            if ($quantity_persons_room["adults"] > $room["max_adults"]) {

                                $quantity_adults_front = $room["max_adults"];
                            } else {
                                $quantity_adults_front = $rates_plan_room_new['quantity_adults'];
                            }

                            if ($quantity_persons_room["child"] > $room["max_child"]) {

                                $quantity_child_front = $room["max_child"];
                            } else {
                                $quantity_child_front = $rates_plan_room_new['quantity_child'];
                            }
                        } else {
                            if (in_array($rate["id"], $ids_rates_channels_charged)) {
                                continue;
                            }
                            if (strtoupper($rate['channel']['code']) === 'HYPERGUEST') {
                                $rates_plan_room_new = $this->getChannelsAvailableRates(
                                    $markupFromsearch,
                                    $quantity_persons_room["adults"],
                                    $quantity_persons_room["child"],
                                    $quantity_persons_room["ages_child"],
                                    $hotel_client['hotel'],
                                    $room
                                );
                            } else {
                                $rates_plan_room_new = $this->getChannelsFirstAvailRate(
                                    $this->getMarkupFromsearch(
                                        $hotel_client['client_markup'],
                                        $hotel_client['hotel']['markup'],
                                        $rate,
                                        $setMarkup
                                    ),
                                    $rate["id"]
                                );
                            }
                        }

                        if (!$zeroRates && $rates_plan_room_new["total_amount"] <= 0) {
                            continue;
                        }

                        if ($rates_plan_room_new && $check_other_step) {
                            $guest_quantity = 1;
                            $rooms_quantity = 1;

                            //calculo de detalle de politicas de cancelacion
                            if ($rate["channel_id"] == 1) {
                                if (count($rates_plan_room_new["calendarys"]) == 0) {
                                    dd($rates_plan_room_new);
                                }
                                $selected_policies_cancelation = collect($rates_plan_room_new["calendarys"][0]["policies_rates"]["policies_cancelation"]);
                            } else {
                                if (count($rates_plan_room_new['policies_cancelation']) == 0) {
                                    $selected_policies_cancelation = collect($rates_plan_room_new["calendarys"][0]["policies_cancelation"]);
                                } else {
                                    $selected_policies_cancelation = collect($rates_plan_room_new["policies_cancelation"]);
                                }
                            }
                            $no_show_apply = [
                                "executive" => Auth::user()->user_type_id,
                                "political_child" => "",
                                "message" => ""
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


                            $supplements = $this->calculateRateSupplementsRequired(
                                $rate["rates_plans_id"],
                                $hotel_client["hotel_id"],
                                $from,
                                $to,
                                $client_id,
                                1,
                                0,
                                [],
                                $rates_plan_room_new['markup'],
                                $language_id
                            );

                            $policy_cancellation_calculated = $this->calculateCancellationlPolicies(
                                $current_date,
                                $check_in,
                                $check_out,
                                $rates_plan_room_new["total_amount"] + $supplements["total_amount"],
                                $selected_policies_cancelation,
                                $guest_quantity,
                                $rooms_quantity
                            );

                            $message = empty($policy_cancellation_calculated['next_penalty']["message"]) ? '' : $policy_cancellation_calculated['next_penalty']["message"];


                            $taxes_and_services = $this->addHotelExtraFees(
                                $applicable_fees,
                                $rate["rate_plan"],
                                ($rates_plan_room_new["total_amount"] + $supplements["total_amount"])
                            );

                            $total_amount = $rates_plan_room_new["total_amount"] + $taxes_and_services["amount_fees"] + $supplements["total_amount"];
                            $total_amount_tax = $taxes_and_services["amount_fees"];

                            $total_amount = number_format($total_amount, 2, '.', '');
                            $total_amount_tax = number_format($total_amount_tax, 2, '.', '');

                            $rates_calendars = $rates_plan_room_new["calendarys"];

                            foreach ($rates_calendars as $idratecalen => $rates_calendar) {
                                unset($rates_calendars[$idratecalen]['policies_rates']['translations']);
                            }

                            $cancellation_details = [];
                            if (isset($policy_cancellation_calculated['selected_policy_cancelation']["policy_cancellation_parameter"])) {
                                foreach ($policy_cancellation_calculated['selected_policy_cancelation']["policy_cancellation_parameter"] as $detail) {
                                    array_push($cancellation_details, [
                                        'to' => $detail["min_day"],
                                        'from' => $detail["max_day"],
                                        'amount' => $detail["amount"] ? $detail["amount"] : 100,
                                        'tax' => $detail["tax"],
                                        'service' => $detail["service"],
                                        'penalty' => $detail["penalty"]["name"],
                                    ]);
                                }
                            } else {
                                array_push($cancellation_details, [
                                    'to' => 0,
                                    'from' => 0,
                                    'amount' => 100,
                                    'tax' => 1,
                                    'service' => 1,
                                    'penalty' => 'total_reservation ',
                                ]);
                            }

                            if ($rate["channel_id"] == 1) {
                                $max_occupancy = $rate["calendarys"][0]["policies_rates"]["max_occupancy"];
                            } else {
                                $max_occupancy = $rate["calendarys"][0]["max_occupancy"];
                            }

                            $rate_plan_description = '';
                            if ($rate['descriptions']) {
                                $rate_plan_description = $rate['descriptions'][0]['value'];
                            }

                            //Agregar tarifa al arreglo
                            if ($rate["channel_id"] == 1) {

                                //                                    return $rate["rate_plan"]; die;

                                //                                $promotions_data_ =
//                                    (isset($rate["rate_plan"]["promotions_data"]) && $rate["rate_plan"]["promotions"])
//                                        ? $rate["rate_plan"]["promotions_data"] : [];

                                $promotions_data_ = [];
                                if (isset($rate["rate_plan"]["promotions_data"]) && $rate["rate_plan"]["promotions"]) {
                                    $promotions_data_ = $rate["rate_plan"]["promotions_data"];
                                } else {
                                    if ($rate["rate_plan"]["promotions"]) {
                                        $promotions_data_ =
                                            RatesPlansPromotions::where(
                                                'rates_plans_id',
                                                $rate["rate_plan"]["id"]
                                            )->get();
                                    }
                                }

                                array_push($rates, [
                                    'rateId' => $rate["id"],
                                    'ratePlanId' => $rate["rate_plan"]["id"],
                                    'promotions_data' => $promotions_data_,
                                    'name_commercial' => $rate["rate_plan"]["name"],
                                    'name' => $rate["rate_plan"]["translations"][0]["value"],
                                    'description' => $rate_plan_description,
                                    'meal_id' => $rate["rate_plan"]["meal"]['id'],
                                    'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"],
                                    'available' => $min_inventory,
                                    'onRequest' => $rate["status"],
                                    'rateProvider' => $rate["channel"]["name"],
                                    'no_show' => $rate["rate_plan"]["translations_no_show"][0]["value"],
                                    'day_use' => $rate["rate_plan"]["translations_day_use"][0]["value"],
                                    'notes' => (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes',
                                    'total' => $total_amount,
                                    'total_taxes_and_services' => $total_amount_tax,
                                    'avgPrice' => $total_amount,
                                    'political' => [
                                        'rate' => [
                                            'name' => $rate["calendarys"][0]["policies_rates"]["name"],
                                            //                                        'message' => "@".$rate["calendarys"][0]["policies_rates"]["translations"][0]['value'],
//                                        'message' => "",
//                                                'message' => json_encode( $rate["calendarys"][0]["policies_rates"] ),
                                            'message' => (isset($rate["calendarys"][0]["policies_rates"]["translations"]) ? $rate["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
                                            'max_occupancy' => $max_occupancy,
                                            'example' => $rate
                                        ],
                                        'cancellation' => [
                                            "name" => $message,
                                            "details" => $cancellation_details,
                                        ],
                                        "no_show_apply" => $no_show_apply
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
                                            'quantity_adults' => $quantity_adults_front,
                                            'quantity_child' => $quantity_child_front,
                                            'quantity_extras' => $rates_plan_room_new['quantity_extras'],
                                            'quantity_adults_total' => $quantity_adults_front,
                                            'quantity_child_total' => $quantity_child_front,
                                            'quantity_extras_total' => $rates_plan_room_new['quantity_extras'],
                                            'total_amount_adult' => $rates_plan_room_new['total_amount_adult'],
                                            'total_amount_child' => $rates_plan_room_new['total_amount_child'],
                                            'total_amount_infants' => 0,
                                            'total_amount_extras' => $rates_plan_room_new['total_amount_extra'],
                                            'people_coverage' => $quantity_adults_front + $quantity_child_front,
                                            'quantity_inventory_taken' => 1,
                                            'amount_days' => $rates_calendars,
                                        ],
                                    ],
                                    'show_message_error' => false,
                                    'message_error' => '',
                                ]);

                            } else {
                                if (!in_array($rate["id"], $ids_rates_channels_charged)) {
                                    //                                    $promotions_data_ =
//                                        (isset($rate["rate_plan"]["promotions_data"]) && $rate["rate_plan"]["promotions"])
//                                            ? $rate["rate_plan"]["promotions_data"] : [];

                                    $promotions_data_ = [];
                                    if (isset($rate["rate_plan"]["promotions_data"]) && $rate["rate_plan"]["promotions"]) {
                                        $promotions_data_ = $rate["rate_plan"]["promotions_data"];
                                    } else {
                                        if ($rate["rate_plan"]["promotions"]) {
                                            $promotions_data_ =
                                                RatesPlansPromotions::where(
                                                    'rates_plans_id',
                                                    $rate["rate_plan"]["id"]
                                                )->get();
                                        }
                                    }

                                    array_push($rates, [
                                        'rateId' => $rate["id"],
                                        'ratePlanId' => $rate["rate_plan"]["id"],
                                        'promotions_data' => $promotions_data_,
                                        'name_commercial' => $rate["rate_plan"]["name"],
                                        'name' => $rate["rate_plan"]["translations"][0]["value"],
                                        'description' => $rate_plan_description,
                                        'meal_id' => $rate["rate_plan"]["meal"]['id'],
                                        'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"],
                                        'available' => $min_inventory,
                                        'onRequest' => $rate["status"],
                                        'rateProvider' => $rate["channel"]["name"],
                                        'no_show' => $rate["rate_plan"]["translations_no_show"][0]["value"],
                                        'day_use' => $rate["rate_plan"]["translations_day_use"][0]["value"],
                                        'notes' => (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes',
                                        'total' => $total_amount,
                                        'total_taxes_and_services' => $total_amount_tax,
                                        'avgPrice' => $total_amount,
                                        'political' => [
                                            'rate' => [
                                                'name' => $rate["calendarys"][0]["policies_rates"]["name"],
                                                //                                            'message' => "@@".$rate["calendarys"][0]["policies_rates"]["translations"][0]['value'],
                                                'message' => (isset($rate["calendarys"][0]["policies_rates"]["translations"]) ? $rate["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
                                                'max_occupancy' => $max_occupancy,
                                                'example' => $rate
                                            ],
                                            'cancellation' => [
                                                "name" => $message,
                                                "details" => $cancellation_details,
                                            ],
                                            "no_show_apply" => $no_show_apply
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
                                        'show_message_error' => false,
                                        'message_error' => '',
                                    ]);
                                    array_push($ids_rates_channels_charged, $rate["id"]);
                                }
                            }
                        }
                    }

                    if (count($rates) == 0) {
                        continue;
                    }

                    //agregar habitacion al arreglo
                    array_push($rooms, [
                        'room_id' => $room["id"],
                        'room_type' => $room['room_type']['translations'][0]['value'],
                        'occupation' => $room['room_type']['occupation'],
                        'bed_additional' => $room["bed_additional"],
                        'name' => $room_name,
                        'description' => $room_description,
                        'gallery' => $room_gallery,
                        'max_capacity' => $room["max_capacity"],
                        'max_adults' => $room["max_adults"],
                        'max_child' => $room["max_child"],
                        'rates' => $rates,
                    ]);


                    //} fin cierre condicional de habitaciones

                }
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
                            array_push($room_gallery, $image["url"]);
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
                                array_push($rates_calendars, [
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
                                ]);
                            }

                            //calculo de detalle de politicas de cancelacion
                            if ($rate['rate']['channel_id'] == 1) {
                                $selected_policies_cancelation = collect($tarifas_seleccionada['rate']["calendarys"][0]["policies_rates"]["policies_cancelation"]);
                            } else {
                                if (count($tarifas_seleccionada['rate']['policies_cancelation']) == 0) {
                                    $selected_policies_cancelation = collect($tarifas_seleccionada['rate']["calendarys"][0]["policies_cancelation"]);
                                } else {
                                    $selected_policies_cancelation = collect($tarifas_seleccionada['rate']['policies_cancelation']);
                                }
                            }

                            $selected_policy_cancelation = $this->getCancellationPolicyByTypeFit(
                                $selected_policies_cancelation,
                                $guest_quantity,
                                $rooms_quantity
                            );

                            $cancellation_details = [];
                            if (isset($selected_policy_cancelation["policy_cancellation_parameter"])) {
                                foreach ($selected_policy_cancelation["policy_cancellation_parameter"] as $detail) {
                                    array_push($cancellation_details, [
                                        'to' => $detail["min_day"],
                                        'from' => $detail["max_day"],
                                        'amount' => $detail["amount"],
                                        'tax' => $detail["tax"],
                                        'service' => $detail["service"],
                                    ]);
                                }
                            }

                            $message = $rate["policy_cancellation"]["message"];

                            //                            if ($rate["rate"]["channel_id"] == 1) {
//                                $message = $rate["policy_cancellation"]["message"];
//                            } else {
//                                $message = "";
//                            }

                            $rate_plan_description = '';
                            if ($rate['rate']['descriptions']) {
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
                                        RatesPlansPromotions::where(
                                            'rates_plans_id',
                                            $rate["rate"]["rate_plan"]["id"]
                                        )->get();
                                }
                            }

                            //Agregar tarifa al arreglo
                            array_push($rates, [
                                'rateId' => $rate["rate"]["id"],
                                'ratePlanId' => $rate["rate"]["rate_plan"]["id"],
                                'promotions_data' => $promotions_data_,
                                'name_commercial' => $rate["rate"]["rate_plan"]["name"],
                                'name' => $rate["rate"]["rate_plan"]["translations"][0]["value"],
                                'description' => $rate_plan_description,
                                'meal_id' => $rate["rate"]["rate_plan"]["meal"]['id'],
                                'meal_name' => $rate["rate"]["rate_plan"]["meal"]["translations"][0]["value"],
                                'available' => $min_inventory,
                                'inventories' => $rate["rate"]["inventories"],
                                'onRequest' => 1,
                                'rateProvider' => $rate["rate"]["channel"]["name"],
                                'no_show' => $rate["rate"]["rate_plan"]["translations_no_show"][0]["value"],
                                'day_use' => $rate["rate"]["rate_plan"]["translations_day_use"][0]["value"],
                                'notes' => (count($rate["rate"]["rate_plan"]["translations_notes"]) > 0) ? $rate["rate"]["rate_plan"]["translations_notes"][0]["value"] : 'No notes',
                                'total' => $total_amount,
                                'total_taxes_and_services' => $total_amount_tax,
                                'avgPrice' => $total_amount,
                                'political' => [
                                    'rate' => [
                                        'name' => $rate["rate"]["calendarys"][0]["policies_rates"]["name"],
                                        //                                'message' => "@@@".$rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'],
                                        'message' => (isset($rate["rate"]["calendarys"][0]["policies_rates"]["translations"]) ? $rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
                                        'example' => $rate
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
                            ]);
                        }
                        //agregar habitacion al arreglo
                        array_push($best_options["rooms"], [
                            'room_id' => $opcion["id"],
                            'room_type' => $opcion['room_type']['translations'][0]['value'],
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
                        ]);
                    }

                }
            }

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

            if ($princeHotel == 0) {
                continue;
            }

            $code_ = ChannelHotel::where("hotel_id", $hotel_client["hotel"]["id"])->where("channel_id", 1)->first();

            $zone_name = '';
            $district_name = '';
            if (!empty($hotel_client["hotel"]["zone"]) and count($hotel_client["hotel"]["zone"]["translations"]) > 0) {
                $zone_name = $hotel_client["hotel"]["zone"]["translations"][0]["value"];
            }

            if (!empty($hotel_client["hotel"]["district"]) and count($hotel_client["hotel"]["district"]["translations"]) > 0) {
                $district_name = $hotel_client["hotel"]["district"]["translations"][0]["value"];
            }

            if (!count($rooms)) {
                continue;
            }

            array_push($hotels[0]["city"]["hotels"], [
                "id" => $hotel_client["hotel"]["id"],
                "code" => $code_ ? $code_->code : $hotel_client["hotel"]["id"],
                "flag_new" => $hotel_client["hotel"]["flag_new"],
                "date_end_flag_new" => $hotel_client["hotel"]["date_end_flag_new"],
                "name" => $hotel_client["hotel"]["name"],
                "country" => $hotel_client["hotel"]["country"]["translations"][0]["value"],
                "state" => $hotel_client["hotel"]["state"]["translations"][0]["value"],
                "city" => $hotel_client["hotel"]["city"]["translations"][0]["value"],
                "district" => $district_name,
                "zone" => $zone_name,
                "description" => $hotel_description,
                "address" => $hotel_address,
                "summary" => $hotel_summary,
                "notes" => (isset($hotel_client["hotel"]["notes"])) ? $hotel_client["hotel"]["notes"] : '',
                "chain" => $hotel_client["hotel"]["chain"]["name"],
                "logo" => $hotel_logo,
                "category" => (int) ($hotel_client["hotel"]["stars"]),
                "type" => $hotel_client["hotel"]["hoteltype"]["translations"][0]["value"],
                "class" => $hotel_client["hotel"]["typeclass"]["translations"][0]["value"],
                "color_class" => $hotel_client["hotel"]["typeclass"]["color"],
                "price" => $princeHotel,
                "coordinates" => [
                    'latitude' => $hotel_client["hotel"]["latitude"],
                    'longitude' => $hotel_client["hotel"]["longitude"],
                ],
                "popularity" => $hotel_client["hotel"]["preferential"],
                "favorite" => $this->checkHotelFavorite($hotel_client["hotel"]["id"]),
                "checkIn" => $hotel_client["hotel"]["check_in_time"],
                "checkOut" => $hotel_client["hotel"]["check_out_time"],
                "political_children" => [
                    "child" => [
                        "allows_child" => $hotel_client["hotel"]["allows_child"],
                        "min_age_child" => $hotel_client["hotel"]["min_age_child"],
                        "max_age_child" => $hotel_client["hotel"]["max_age_child"]
                    ],
                    "infant" => [
                        "allows_teenagers" => $hotel_client["hotel"]["allows_teenagers"],
                        "min_age_teenagers" => $hotel_client["hotel"]["min_age_teenagers"],
                        "max_age_teenagers" => $hotel_client["hotel"]["max_age_teenagers"]
                    ]
                ],
                "amenities" => $amenities,
                "galleries" => $hotel_gallery,
                "best_options" => $best_options,
                "rooms" => $rooms,
                "best_option_taken" => false,
                "best_option_cart_items_id" => [],
            ]);
        }

        $hotels[0]["city"]["min_price_search"] = number_format($min_price_search, 2, '.', '');
        $hotels[0]["city"]["max_price_search"] = number_format($max_price_search, 2, '.', '');
        $hotels[0]["city"]["quantity_hotels"] = count($hotels[0]["city"]["hotels"]);

        $token_search_frontend = $faker->unique()->uuid;
        $hotels[0]["city"]["token_search_frontend"] = $token_search_frontend;

        $this->storeTokenSearchHotels($token_search_frontend, $hotels, $this->expiration_search_hotels);

        // validamos el inventario de cada habitacion agregado a la mejor opcion si no sobre pasa.

        /*foreach ($hotels[0]['city']['hotels'] as $index_hotel => $hotel) {

            if (isset($hotel['best_options']) and isset($hotel['best_options']['rooms'])) {

                $inventarioBestOptions = [];
                foreach ($hotel['best_options']['rooms'] as $roomInd => $room) {
                    foreach ($room['rates'] as $tarInd => $tarifa) {
                        if (isset($tarifa['inventories'][0]['bag_room_id'])) {
                            $inventarioBestOptions[$tarifa['inventories'][0]['bag_room_id']][$tarifa['rateId']][] = [
                                'room_id' => $room['room_id'],
                                'rateId' => $tarifa['rateId'],
                                'available' => $tarifa['available'],
//                                'available' => $tarifa['available'],
                                'inventories' => $tarifa['inventories']
                            ];
                        }

                        if (isset($tarifa['inventories'][0]['rate_plan_rooms_id'])) {
                            $inventarioBestOptions[$tarifa['inventories'][0]['rate_plan_rooms_id']][$tarifa['rateId']][] = [
                                'room_id' => $room['room_id'],
                                'rateId' => $tarifa['rateId'],
                                'available' => $tarifa['available'],
//                                'available' => $tarifa['available'],
                                'inventories' => $tarifa['inventories']
                            ];
                        }

                    }
                }

                foreach ($inventarioBestOptions as $inventarioIdBagOrRatePlanRomId => $ratePlansRoomsIds) {
                    $inventarioBolsaOrInventario = '';
                    $totalRooms = 0;
                    foreach ($ratePlansRoomsIds as $roomsSeleccionados) {
                        $inventarioBolsaOrInventario = $roomsSeleccionados[0]['available'];
                        $totalRooms = $totalRooms + count($roomsSeleccionados);
                    }

                    if ($inventarioBolsaOrInventario < $totalRooms) {
                        $hotels[0]['city']['hotels'][$index_hotel]['best_options'] = [];
                        break;
                    }
                }
            }
        }*/

        // echo json_encode($inventarioBestOptions);
        // // // echo json_encode($hotels_client[0]['best_options']);
        // die('');

        $response_z = [
            'success' => true,
            'data' => $hotels,
            'expiration_token' => $this->expiration_search_hotels
        ];

        Cache::put('response_', $response_z, 3600);

        return Response::json($response_z);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveReservationExternalClient(Request $request)
    {
        try {
            if (is_array($request->get('reservations_packages')) and count($request->get('reservations_packages')) > 0) {
                $response = $this->reservationBuildPush($request);
            } else {
                $response = $this->reservationPushExternalClient($request);
            }
            return Response::json(['success' => true, 'data' => [$response]], 200);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getCode()], 400);
        }

    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function reservationBuildPush(Request $request)
    {
        $client_id = $this->getClientId($request->get('client_id'));
        $reference = $request->get('reference');
        $date_init = $request->get('date_init');
        $guests = $request->get('guests');
        $reservations = $request->get('reservations');
        $payment_code = $request->get('payment_code');
        $reservations_services = $request->get('reservations_services');

        $total_amount = ($request->has('total_amount')) ? $request->get('total_amount') : 0;
        $tax_total = ($request->has('total_tax')) ? $request->get('total_tax') : 0;
        $total_discounts = ($request->has('total_discounts')) ? $request->get('total_discounts') : 0;
        $subtotal_amount = ($request->has('subtotal_amount')) ? $request->get('subtotal_amount') : 0;
        $reservations_packages = $request->get('reservations_packages');
        $billing = $request->get('billing');

        $reservation_data = [
            "client_id" => $client_id,
            "file_code" => "",
            "reference" => $reference,
            "reference_new" => $reference,
            "guests" => $guests,
            "payment_code" => $payment_code,
            "total_amount" => $total_amount,
            "total_tax" => $tax_total,
            "subtotal_amount" => $subtotal_amount,
            "total_discounts" => $total_discounts,
            "reservations" => $reservations,
            "reservations_services" => $reservations_services,
            "reservations_packages" => $reservations_packages,
            "billing" => $billing,
            "date_init" => $date_init
        ];

        foreach ($reservations_packages as $package) {
            $date = $package['date_from'];
            $package_id = $package['package_id'];

            $quantity_rooms_sgl = $package['quantity_rooms_sgl'];
            $quantity_rooms_dbl = $package['quantity_rooms_dbl'];
            $quantity_rooms_tpl = $package['quantity_rooms_tpl'];

            $package_plan_rate_category = PackagePlanRateCategory::where(
                'package_plan_rate_id',
                $package['rate_plan_id']
            )->where('type_class_id', $package['type_class'])->select(['id'])->first();
            $services = $this->getServicesByPackage($package_plan_rate_category->id, 'es');
            $array_services_new = $this->updateDateInServices($services, $date, false);
            $services = $array_services_new["services"];
            $quantity_adults = $package["quantity_adults"];
            $quantity_child = $package["quantity_child"];

            foreach ($services as $service) {
                try {
                    if ($service["type"] == "hotel") {
                        foreach ($service["service_rooms"] as $index => $service_room) {
                            $rate_plan_room_model = RatesPlansRooms::find($service_room["rate_plan_room_id"]);
                            $room_model = Room::find($rate_plan_room_model->room_id);
                            $room_type = RoomType::find($room_model->room_type_id);
                            $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                        }

                        try {
                            $hotels_id = [$service["object_id"]];
                            $hotel_id = $service["object_id"];

                            $check_in = $service["date_in"];
                            $check_out = $service["date_out"];

                            $destinationsRequest = Request::createFrom($request);

                            $destinationsRequest->request->add([
                                'client_id' => $client_id,
                                'hotels_id' => $hotels_id,
                            ]);


                            $destinations = $this->destinations($destinationsRequest);

                            $hotelsRequest = Request::createFrom($request);

                            $hotelsRequest->request->add([
                                'client_id' => $client_id,
                                'hotels_id' => $hotels_id,
                                'destiny' => [
                                    'code' => $destinations->getData()[0]->code,
                                    'label' => $destinations->getData()[0]->label
                                ],
                                'date_from' => $check_in,
                                'date_to' => $check_out,
                                'typeclass_id' => 'all',
                                'quantity_rooms' => 1,
                                'quantity_persons_rooms' => [
                                    [
                                        "room" => 1,
                                        "adults" => 1,
                                        "child" => 0,
                                        "ages_child" => [
                                            [
                                                'child' => 1,
                                                'age' => 1,
                                            ],
                                        ],
                                    ],
                                ],
                            ]);

                            $hotel_search = $this->hotels($hotelsRequest);
                            $result_hotel = $hotel_search->getData()->data[0]->city;

                            $token_search = $result_hotel->token_search;


                            foreach ($result_hotel->hotels as $hotel_ind => $hotel) {
                                if ($hotel->id != $hotel_id) {
                                    continue;
                                }

                                $room_request = Request::createFrom($request); //$request->toArray();

                                $rooms_selected = [];
                                //Reserva para Simple
                                foreach ($service["service_rooms"] as $service_room) {
                                    if ($service_room["occupation"] == 1) {
                                        if ($quantity_rooms_sgl > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                //return response()->json($room,200);
                                                foreach ($room->rates as $rate) {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        //return response()->json($room->rates,200);
                                                        $room_request->request->replace([
                                                            'token_search' => $token_search,
                                                            'hotel_id' => $hotel_id,
                                                            'room_id' => $room->room_id,
                                                            'rate_id' => $rate->rateId,
                                                            'rate_plan_id' => $rate->ratePlanId,
                                                            'date_from' => $check_in,
                                                            'date_to' => $check_out,
                                                            'client_id' => $client_id,
                                                            'rooms' => [
                                                                [
                                                                    'quantity_adults' => 1,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                        ]);

                                                        for ($i = 0; $i < $quantity_rooms_sgl; $i++) {
                                                            array_push($reservation_data["reservations"], [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                'package_id' => $package_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "quantity_adults" => $quantity_adults,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => []
                                                            ]);
                                                        }
                                                        break;
                                                    }

                                                }
                                            }
                                        }
                                    }
                                }
                                //Reserva para Doble
                                foreach ($service["service_rooms"] as $service_room) {
                                    if ($service_room["occupation"] == 2) {
                                        if ($quantity_rooms_dbl > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                //return response()->json($room,200);
                                                foreach ($room->rates as $rate) {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        //return response()->json($room->rates,200);
                                                        $room_request->request->replace([
                                                            'token_search' => $token_search,
                                                            'hotel_id' => $hotel_id,
                                                            'room_id' => $room->room_id,
                                                            'rate_id' => $rate->rateId,
                                                            'rate_plan_id' => $rate->ratePlanId,
                                                            'date_from' => $check_in,
                                                            'date_to' => $check_out,
                                                            'client_id' => $client_id,
                                                            'rooms' => [
                                                                [
                                                                    'quantity_adults' => 1,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                        ]);

                                                        for ($i = 0; $i < $quantity_rooms_dbl; $i++) {
                                                            array_push($reservation_data["reservations"], [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                'package_id' => $package_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "quantity_adults" => $quantity_adults,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => []
                                                            ]);
                                                        }
                                                        break;
                                                    }

                                                }
                                            }
                                        }

                                    }
                                }
                                //Reserva para Triple
                                foreach ($service["service_rooms"] as $service_room) {
                                    if ($service_room["occupation"] == 3) {
                                        if ($quantity_rooms_tpl > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                //return response()->json($room,200);
                                                foreach ($room->rates as $rate) {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        //return response()->json($room->rates,200);
                                                        $room_request->request->replace([
                                                            'token_search' => $token_search,
                                                            'hotel_id' => $hotel_id,
                                                            'room_id' => $room->room_id,
                                                            'rate_id' => $rate->rateId,
                                                            'rate_plan_id' => $rate->ratePlanId,
                                                            'date_from' => $check_in,
                                                            'date_to' => $check_out,
                                                            'client_id' => $client_id,
                                                            'rooms' => [
                                                                [
                                                                    'quantity_adults' => 3,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                        ]);
                                                        for ($i = 0; $i < $quantity_rooms_tpl; $i++) {
                                                            array_push($reservation_data["reservations"], [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                'package_id' => $package_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "quantity_adults" => $quantity_adults,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => []
                                                            ]);
                                                        }
                                                        break;
                                                    }

                                                }
                                            }
                                        }

                                    }
                                }
                            }


                        } catch (\Exception $e) {
                            $response = ['success' => false, 'error' => $e->getMessage()];
                        }
                    }
                    if ($service["type"] == "service") {
                        $services_id = [$service["object_id"]];
                        $service_id = $service["object_id"];
                        $check_in = $service["date_in"];

                        $destinationsRequest = Request::createFrom($request);

                        $destinationsRequest->request->add([
                            'client_id' => $client_id,
                            'services_id' => $services_id,
                        ]);

                        $destinations = $this->destinations_services($destinationsRequest);

                        $servicesRequest = ServiceRequest::createFrom($request);

                        $servicesRequest->request->add([
                            'client_id' => (int) $client_id,
                            'origin' => [
                                'code' => $destinations->getData()->data->origins[0]->code,
                                'label' => $destinations->getData()->data->origins[0]->label
                            ],
                            'destiny' => [
                                'code' => $destinations->getData()->data->destinations[0]->code,
                                'label' => $destinations->getData()->data->destinations[0]->label
                            ],
                            'lang' => 'es',
                            'date' => $check_in,
                            'services_id' => $services_id,
                            'quantity_persons' => [
                                "adults" => (int) $quantity_adults,
                                "child" => (int) $quantity_child,
                                "ages_child" => [
                                    [
                                        'child' => 1,
                                        'age' => 1,
                                    ],
                                ],
                            ],
                        ]);

                        $service_search = $this->services($servicesRequest);

                        $result_service = $service_search->getData()->data;

                        $token_search = $result_service->token_search;
                        array_push($reservation_data["reservations_services"], [
                            'token_search' => $token_search,
                            'service_ident' => 0,
                            'service_id' => $service_id,
                            'package_id' => $package_id,
                            'rate_plan_id' => $result_service->services[0]->rate->id,
                            'reservation_time' => empty($result_service->services[0]->reservation_time) ? '00:00' : $result_service->services[0]->reservation_time,
                            'date_from' => $check_in,
                            "guest_note" => "",
                            'quantity_adults' => $quantity_adults,
                            'quantity_child' => $quantity_child,
                            'child_ages' => []
                        ]);
                    }
                } catch (\Exception $e) {
                    return Response::json([
                        'success' => false,
                        //                            'message' => $result_service,
                        'message' => 'object_id: ' . $service["object_id"] . ' - Line:' . $e->getLine(),
                    ]);
                }
            }

        }
        $reservationRequest = new \Illuminate\Http\Request();
        $reservationRequest->setMethod('POST');
        $reservationRequest->request->add($reservation_data);
        return $this->reservationPushExternalClient($reservationRequest);
        ;
    }

    public static function getReservationPending(Request $request)
    {
        try {
            $params = [];
            if ($request->input('booking_code')) {
                $params['booking_code'] = $request->input('booking_code');
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
                $params['from_date'] = Carbon::parse($request->input('from_date'))->format('Y-m-d') . ' 00:00:00';
            }
            if ($request->input('to_date')) {
                $params['to_date'] = Carbon::parse($request->input('to_date'))->format('Y-m-d') . ' 23:59:56';
            }

            if ($request->input('user_type_id')) {
                $params['user_type_id'] = $request->input('user_type_id');
            }

            $reservations = Reservation::getAllReservationPaginate($params);

            $repsonse = ['success' => true, 'data' => $reservations];
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }


}
