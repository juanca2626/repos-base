<?php


namespace App\Http\Traits;


use App\Markup;
use App\Country;
use App\Service;
use App\Currency;
use App\Language;
use Carbon\Carbon;
use App\ServiceRate;
use App\ClientSeller;
use App\QuoteService;
use App\MarkupService;
use App\ServiceOrigin;
use App\ServiceRatePlan;
use App\ServiceSchedule;
use App\QuoteServiceRate;
use App\ServiceOperation;
use App\ServiceDestination;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

trait Services
{

    private $selected_tokens_search = [];
    public $expiration_search_supplement = 1440;// 24 horas

    /**
     * @param $service_id
     * @param $client_id
     * @param $adult
     * @param $child
     * @param $date_in
     * @param $quote_id
     * @param $option
     */
    private function calculateAmountServiceService(
        $service_id,
        $client_id,
        $adult,
        $child,
        $date_in,
        $quote_id,
        $option = 1
    )
    {
        $markup = 0;

        $service_rate_id = QuoteServiceRate::where('quote_service_id',
            $service_id)->first()->service_rate_id;

        $quote_service = QuoteService::where('id', $service_id)->first();

        $markup_rate_client = MarkupService::where('client_id', $client_id)
            ->where('service_id', $quote_service->object_id)
            ->where('period', Carbon::parse($date_in)->year)
            ->whereNull('deleted_at')
            ->first();

        if ($markup_rate_client == null) {
            $markup_general = Markup::where('client_id', $client_id)->where('period',
                Carbon::parse($date_in)->year)->first();
            $markup = $markup_general->service;
        } else {
            $markup = $markup_rate_client->markup;
        }
        $pax_amount = ServiceRatePlan::where('service_rate_id', $service_rate_id)
            ->where('date_from', '<=', $date_in)
            ->where('date_to', '>=', $date_in)
            ->where('pax_from', '<=', $adult + $child)
            ->where('pax_to', '>=', $adult + $child)
            ->first();

        DB::raw('SET SQL_SAFE_UPDATES=0;');

        DB::table('quote_service_amounts')->where('quote_service_id', $service_id)->delete();

        if ($pax_amount != null) {
            DB::table('quote_service_amounts')->insert([
                'quote_service_id' => $service_id,
                'date_service' => $date_in,
                'price_per_night' => 0,
                'price_per_night_without_markup' => 0,
                'price_adult_without_markup' => $pax_amount->price_adult,
                'price_adult' => $pax_amount->price_adult + ($pax_amount->price_adult * ($markup / 100)),
                'price_child_without_markup' => $pax_amount->price_child,
                'price_child' => $pax_amount->price_child + ($pax_amount->price_child * ($markup / 100)),
                'created_at' => Carbon::now()
            ]);
        } else {
            DB::table('quote_service_amounts')->insert([
                'quote_service_id' => $service_id,
                'date_service' => $date_in,
                'price_per_night' => 0,
                'price_per_night_without_markup' => 0,
                'price_adult_without_markup' => 0,
                'price_adult' => 0,
                'price_child_without_markup' => 0,
                'price_child' => 0,
                'created_at' => Carbon::now()
            ]);
        }
    }

    /**
     * @param $services
     * @param $lang
     */
    private function getInformationServices($services, $lang)
    {
        $language_id = Language::where('iso', $lang)->where('state', 1)->first();
        if ($language_id) {
            $services = (new \App\Service)->getInformationServices($services, $language_id->id);
        } else {
            throw new \Exception('Language not found');
        }
        return $services;
    }

    public function destinations_services(Request $request)
    {
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];

        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        try {
            $withCountry = ($request->has('with_country') and $request->get('with_country') == true) ? true : false;
            if ($request->get('services_id')) {
                $service_client = $this->getClientServices($client_id, Carbon::now('America/Lima')->year, true,
                    $request->get('services_id'));
            } else {
                $service_client = $this->getClientServices($client_id, Carbon::now('America/Lima')->year, true);
            }

            // obtengo los destinos
            $services_destination = $this->getDestinationsServices($service_client, ServiceDestination::class);
            $destinations_country = $this->checkServiceCountry($services_destination);
            $destinations_country_state = $this->checkServiceCountryState($services_destination);

            $destinations_country_state_city = $this->checkServiceCountryStateCity($services_destination);
            $destinations_country_state_city_zone = $this->checkServiceCountryStateCityDistrict($services_destination);
            $destinations = array_merge($destinations_country_state, $destinations_country_state_city);
            $destinations = array_merge($destinations, $destinations_country_state_city_zone);
            if ($withCountry) {
                $destinations = array_merge($destinations, $destinations_country);
            }
            $destinations = array_map("unserialize", array_unique(array_map("serialize", $destinations)));
            $destination_select = [];

            foreach ($destinations as $destination) {
                array_push($destination_select, [
                    "code" => $destination["ids"],
                    "label" => $destination["description"],
                ]);
            }

            // obtengo los origenes
            $services_origins = $this->getDestinationsServices($service_client, ServiceOrigin::class);
            $origin_country_state = $this->checkServiceCountryState($services_origins);

            $origin_country_state_city = $this->checkServiceCountryStateCity($services_origins);
            $origin_country_state_city_zone = $this->checkServiceCountryStateCityDistrict($services_origins);
            $origins = array_merge($origin_country_state, $origin_country_state_city);
            $origins = array_merge($origins, $origin_country_state_city_zone);
            $origins = array_map("unserialize", array_unique(array_map("serialize", $origins)));
            $origin_select = [];

            foreach ($origins as $origin) {
                array_push($origin_select, [
                    "code" => $origin["ids"],
                    "label" => $origin["description"],
                ]);
            }
            sort($destination_select);
            sort($origin_select);
            $data = [
                'origins' => $origin_select,
                'destinations' => $destination_select,
            ];

            return Response::json(['success' => true, 'data' => $data]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }

    private function checkServiceCountry($hotels_client)
    {
        $destinations = [];
        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["country"]["id"],
                    "description" => $hotel_client["country"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel_client["country"]["id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["country"]["id"],
                        "description" => $hotel_client["country"]["translations"][0]["value"],
                    ]);
                }
            }
        }


        return $destinations;
    }

    private function checkServiceCountryStateCity($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                $city_name = (isset($hotel_client["city"]["translations"][0]["value"])) ? ', ' . $hotel_client["city"]["translations"][0]["value"] : '';
                $city_id = ($hotel_client["city_id"] !== null) ? ',' . $hotel_client["city_id"] : '';
                array_push($destinations, [
                    "ids" => $hotel_client["country"]["id"] . ',' . $hotel_client["state"]["id"] . $city_id,
                    "description" => $hotel_client["country"]["translations"][0]["value"] . ', ' . $hotel_client["state"]["translations"][0]["value"] . $city_name,
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == $hotel_client["country"]["id"] . ',' . $hotel_client["state"]["id"] . ',' . $hotel_client["city_id"]) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $city_name = (isset($hotel_client["city"]["translations"][0]["value"])) ? ', ' . $hotel_client["city"]["translations"][0]["value"] : '';
                    $city_id = ($hotel_client["city_id"] !== null) ? ',' . $hotel_client["city_id"] : '';
                    array_push($destinations, [
                        "ids" => $hotel_client["country"]["id"] . ',' . $hotel_client["state"]["id"] . $city_id,
                        "description" => $hotel_client["country"]["translations"][0]["value"] . ', ' . $hotel_client["state"]["translations"][0]["value"] . $city_name,
                    ]);
                }
            }
        }
        return $destinations;
    }

    private function checkServiceCountryStateCityDistrict($hotels_client)
    {
        $destinations = [];
        foreach ($hotels_client as $hotel_client) {

            $id_string_country_id = $hotel_client["country"]["id"];

            $id_string_state_id = "," . $hotel_client["state"]["id"];

            $id_string_city_id = ($hotel_client["city_id"] !== null) ? "," . $hotel_client["city_id"] : '';

            $id_string_zone_id = ($hotel_client["zone_id"] !== null) ? "," . $hotel_client["zone_id"] : '';

            $ids_string = $id_string_country_id . $id_string_state_id . $id_string_city_id . $id_string_zone_id;

            $country_name = $hotel_client["country"]["translations"][0]["value"];
            $state_name = ',' . $hotel_client["state"]["translations"][0]["value"];


            $city_name = $hotel_client["city"] !== null ? ', ' . $hotel_client["city"]["translations"][0]["value"] : '';
            $zone_name = $hotel_client["zone"] !== null ? ', ' . $hotel_client["zone"]["translations"][0]["value"] : '';

            if ($id_string_zone_id) {
                if (count($destinations) === 0) {
                    array_push($destinations, [
                        "ids" => $ids_string,
                        "description" => $country_name . $state_name . $city_name . $zone_name,
                    ]);
                } else {
                    $exists = false;
                    for ($i = 0; $i < count($destinations); $i++) {
                        if ($destinations[$i]["ids"] == $ids_string) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        array_push($destinations, [
                            "ids" => $ids_string,
                            "description" => $country_name . $state_name . $city_name . $zone_name,
                        ]);
                    }
                }
            }
        }
        return $destinations;
    }

    private function checkServiceCountryState($hotels_client)
    {
        $destinations = [];
        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["country"]["id"] . ',' . $hotel_client["state"]["id"],
                    "description" => $hotel_client["country"]["translations"][0]["value"] . ", " . $hotel_client["state"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel_client["country"]["id"] . ',' . $hotel_client["state"]["id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["country"]["id"] . ',' . $hotel_client["state"]["id"],
                        "description" => $hotel_client["country"]["translations"][0]["value"] . ", " . $hotel_client["state"]["translations"][0]["value"],
                    ]);
                }
            }
        }


        return $destinations;
    }

    public function getFormatItinerary($itinerary_text)
    {
        /*
        $itinerary_text = strip_tags(htmlDecode(htmlspecialchars_decode($itinerary_text,
            ENT_QUOTES)));
        // Saltos de linea y #title
        $explode_itinerary_ = explode("\n", $itinerary_text);
        $explode_itinerary_string_ = '';
        for ($ii = 0; $ii < count($explode_itinerary_); $ii++) {
            $explode_title = explode("#title", strtolower($explode_itinerary_[$ii]));
            if (count($explode_title) > 1) {
                $explode_itinerary_[$ii] = str_replace("#title", '', $explode_itinerary_[$ii]);
                $explode_itinerary_[$ii] = str_replace("#Title", '', $explode_itinerary_[$ii]);
                $explode_itinerary_string_ = $explode_itinerary_string_ . '<br> <b>' . $explode_itinerary_[$ii] . " </b>";
            } else {
                $explode_itinerary_string_ = $explode_itinerary_string_ . " <br>" . $explode_itinerary_[$ii];
            }
        }
        $itinerary_text = $explode_itinerary_string_;
        */

//        return $itinerary_text;

        $itinerary = [];
        $textSearch = "###";
        $count = substr_count($itinerary_text, $textSearch);

        $pattern = '/^<\/p><p(?:\s[^>]*)?>/';
        $itinerary_text = preg_replace($pattern, '<p>', $itinerary_text);

        $pattern = '/<\/p><p(?:\s[^>]*)?>$/';
        $itinerary_text = preg_replace($pattern, '</p>', $itinerary_text);

        $textExplode = explode($textSearch, $itinerary_text);

//        return [[
//            'day' => 1,
//            'description' => ltrim($itinerary_text)
//        ]];

        if ($count > 0) {
            $day = 1;
            for ($j = 0; $j <= $count; $j++) {
                $texto = $textExplode[$j];
                $itinerary[] = [
                    'count' => $count,
                    'original' => $itinerary_text,
                    'day' => $day,
                    'description' => $texto
                ];
                $day++;
            }
        } else {
            $itinerary[] = [
                'day' => 1,
                'description' => $itinerary_text
            ];
        }

        return $itinerary;
    }

    public function getSupplementsService(
        $supplements_params,
        $date_to,
        $child_min_age,
        $language_id,
        $dayOfWeek,
        $quantity_adults,
        $quantity_child = 0,
        $ages_child = [],
        $age_child_config = [],
        $unit_duration = [],
        $duration = 1,
        $markup = 0,
        $supplements_has = true
    )
    {
        $supplements_data = [
            "total_amount" => 0,
            "total_adult_amount" => 0,
            "total_child_amount" => 0,
            "total_price_per_person" => 0,
            "supplements" => [],
            "optional_supplements" => [],
        ];
        if ($markup <= 0) {
            return $supplements_data;
        }

        if ($supplements_has) {
            $quantity_total_pax = $quantity_adults + $quantity_child;
            if (count($supplements_params) > 0) {
                $faker = Faker::create();
                $services_id = array_column($supplements_params, 'object_id');

                //Todo Buscamos los suplementos con sus validaciones
                $getSupplementsQuery = $this->getSupplementServicesQuery($services_id, $quantity_total_pax,
                    $language_id, $child_min_age);
                //Todo Verifico que me traiga los suplementos
                if ($getSupplementsQuery->count() > 0) {
                    //Todo Recorremos los suplementos de la tabla service_supplements
                    foreach ($supplements_params as $key => $supplement) {
                        $type_of_supplement = $supplement['type'];
                        $supplement_service = $getSupplementsQuery->first(function ($value) use ($supplement) {
                            return $value->id == $supplement['object_id'];
                        });

                        //Todo Si encuentra el suplemento en el query seguimos
                        if ($supplement_service) {
                            $supplementCollection = collect();
                            $days_to_charge = $this->getDayToCharge($date_to, $supplement['days_to_charge']);
                            $dates = $this->getAllDaysSupplement($date_to, $supplement['days_to_charge']);
                            //Todo obtengo las tarifas del suplemento
                            $getSupplementRatePlans = $this->getRatePlansSupplement($supplement['object_id'], $dates,
                                $language_id, $quantity_total_pax);
                            $days_to_charge = $this->updateDatesCharge($days_to_charge, $getSupplementRatePlans);

                            //Todo Si el suplemento es obligatorio
                            if ($type_of_supplement == 'required') {
                                $token_search = $faker->unique()->uuid;
                                //Todo Recorremos los dias que tiene como obligatorio a cobrar
                                $pricesCharge = collect();
                                foreach ($days_to_charge['charge'] as $date) {
                                    $rate_plan_date = $getSupplementRatePlans->first(function ($value) use ($date) {
                                        return $value['date'] == $date['day'] && $value['available'] === true;
                                    });
                                    if ($rate_plan_date) {
                                        $prices_required = $this->calculateAmountSupplement($rate_plan_date['rate']['service_rate_plans'],
                                            $quantity_adults, $quantity_child, $markup);
                                        $pricesCharge->add($prices_required);
                                        $supplementCollection->add([
                                            'date' => $date['day'],
                                            'supplement' => $supplement_service,
                                            'adults' => $quantity_adults,
                                            'child' => $quantity_child,
                                            'rate' => $rate_plan_date['rate'],
                                            'total_prices' => $prices_required
                                        ]);
                                    }
                                }

                                if ($pricesCharge->count() > 0) {
                                    $supplements_data['total_amount'] = $pricesCharge->sum('total_amount');
                                    $supplements_data['total_price_per_person'] = $pricesCharge->sum('price_per_person');
                                    $supplements_data['total_adult_amount'] = $pricesCharge->sum('total_adult_amount');
                                    $supplements_data['total_child_amount'] = $pricesCharge->sum('total_child_amount');
                                    $supplements_data['supplements'][] = [
                                        'id' => $supplement_service->id,
                                        'name' => $supplement_service->service_translations[0]->name,
                                        'code' => $supplement_service->aurora_code,
                                        'type' => $type_of_supplement,
                                        'charge_all_pax' => (boolean)$supplement['charge_all_pax'],
                                        'days' => $days_to_charge,
                                        'selected' => false,
                                        'token_search' => $token_search,
                                        'params' => [
                                            'adults' => $quantity_adults,
                                            'child' => $quantity_child,
                                            'dates' => []
                                        ],
                                        'rate' => [
                                            'id' => $supplement_service->service_rate[0]->id,
                                            'price_per_adult' => $pricesCharge->sum('price_per_adult'),
                                            'price_per_child' => $pricesCharge->sum('price_per_child'),
                                            'total_adult_amount' => $pricesCharge->sum('total_adult_amount'),
                                            'total_child_amount' => $pricesCharge->sum('total_child_amount'),
                                            'price_per_person' => $pricesCharge->sum('price_per_person'),
                                            'total_amount' => $pricesCharge->sum('total_amount'),
                                        ]
                                    ];

                                    //Todo Guardamos la busqueda en cache
                                    $this->storeTokenSearchSupplements($token_search, $supplementCollection,
                                        $this->expiration_search_supplement);
                                }
                            } else {
                                $token_search = $faker->unique()->uuid;
                                //Todo Recorremos los dias que tiene como obligatorio a cobrar
                                $pricesCharge = collect();
                                foreach ($days_to_charge['charge'] as $date) {
                                    $rate_plan_date = $getSupplementRatePlans->first(function ($value) use ($date) {
                                        return $value['date'] == $date['day'] && $value['available'] === true;
                                    });
                                    if ($rate_plan_date) {
                                        $prices_required = $this->calculateAmountSupplement($rate_plan_date['rate']['service_rate_plans'],
                                            $quantity_adults, $quantity_child, $markup);
                                        $pricesCharge->add($prices_required);
                                        $supplementCollection->add([
                                            'date' => $date['day'],
                                            'supplement' => $supplement_service,
                                            'adults' => $quantity_adults,
                                            'child' => $quantity_child,
                                            'rate' => $rate_plan_date['rate'],
                                            'total_prices' => $prices_required
                                        ]);

                                    }
                                }


                                if (count($days_to_charge['charge']) === 0) {
                                    foreach ($days_to_charge['not_charge'] as $date) {
                                        $rate_plan_date = $getSupplementRatePlans->first(function ($value) use ($date) {
                                            return $value['date'] == $date['day'] && $value['available'] === true;
                                        });
                                        if ($rate_plan_date) {
                                            $prices_required = $this->calculateAmountSupplement($rate_plan_date['rate']['service_rate_plans'],
                                                $quantity_adults, $quantity_child, $markup);
                                            $pricesCharge->add($prices_required);
                                            $supplementCollection->add([
                                                'date' => $date['day'],
                                                'supplement' => $supplement_service,
                                                'adults' => $quantity_adults,
                                                'child' => $quantity_child,
                                                'rate' => $rate_plan_date['rate'],
                                                'total_prices' => $prices_required
                                            ]);

                                        }
                                    }
                                }

                                //Si hay precios para
                                if ($pricesCharge->count() > 0) {
                                    $supplements_data['optional_supplements'][] = [
                                        'id' => $supplement_service->id,
                                        'name' => $supplement_service->service_translations[0]->name,
                                        'code' => $supplement_service->aurora_code,
                                        'type' => $type_of_supplement,
                                        'charge_all_pax' => (boolean)$supplement['charge_all_pax'],
                                        'days' => $days_to_charge,
                                        'selected' => false,
                                        'token_search' => $token_search,
                                        'params' => [
                                            'adults' => $quantity_adults,
                                            'child' => $quantity_child,
                                            'dates' => []
                                        ],
                                        'rate' => [
                                            'id' => $supplement_service->service_rate[0]->id,
                                            'price_per_adult' => $pricesCharge->sum('price_per_adult'),
                                            'price_per_child' => $pricesCharge->sum('price_per_child'),
                                            'total_adult_amount' => $pricesCharge->sum('total_adult_amount'),
                                            'total_child_amount' => $pricesCharge->sum('total_child_amount'),
                                            'price_per_person' => $pricesCharge->sum('price_per_person'),
                                            'total_amount' => $pricesCharge->sum('total_amount'),
                                        ]
                                    ];

                                    //Todo Guardamos la busqueda en cache
                                    $this->storeTokenSearchSupplements($token_search, $supplementCollection,
                                        $this->expiration_search_supplement);
                                }
                            }

                        }
                    }

                }
            }
        }

        return $supplements_data;
    }

    public function calculateAmountSupplement($rates_plans, $quantity_adults, $quantity_child, $markup)
    {
        $prices = collect([
            'price_per_adult' => 0,
            'price_per_child' => 0,
            'price_per_person' => 0,
            'total_adult_amount' => 0,
            'total_child_amount' => 0,
            'total_amount' => 0,
        ]);
        foreach ($rates_plans as $rate) {
            $prices['price_per_adult'] += (($rate['price_adult'] * 1) + (($rate['price_adult'] * 1) * ($markup / 100)));
            if ($quantity_child > 0) {
                $prices['price_per_child'] += (($rate['price_child'] * 1) + (($rate['price_child'] * 1) * ($markup / 100)));
            }
            $price_per_person_adult = (float)roundLito($prices['price_per_adult']);
            $price_per_person_child = (float)roundLito($prices['price_per_child']);
            $prices['price_per_adult'] = $price_per_person_adult;
            $prices['price_per_child'] = $price_per_person_child;
            $prices['total_adult_amount'] = (float)($price_per_person_adult * $quantity_adults);
            $prices['total_child_amount'] = (float)($price_per_person_child * $quantity_child);
            $total_amount_rate = $prices['total_adult_amount'] + $prices['total_child_amount'];
            $price_per_person = $total_amount_rate / ($quantity_adults + $quantity_child);
            $prices['price_per_person'] = $price_per_person;
            $prices['total_amount'] = $total_amount_rate;
        }
        return $prices;
    }

    public function getSupplementServicesQuery(
        $services_id,
        $totalPax,
        $language_id,
        $child_min_age_search = null
    )
    {
        $query_search = Service::select([
            'id',
            'aurora_code',
            'name',
            'equivalence_aurora',
            'allow_guide',
            'allow_child',
            'allow_infant',
            'infant_min_age',
            'infant_max_age',
            'unit_id',
            'unit_duration_id',
            'service_type_id',
            'duration',
            'pax_min',
            'pax_max',
            'min_age',
            'status',
        ])
            ->where('status', 1)
            ->where('type', 'supplement')
            ->where('pax_min', '<=', $totalPax)
            ->where('pax_max', '>=', $totalPax)
            ->with([
                'tax' => function ($query) {
                    $query->select('amount', 'service_id');
                    $query->where('status', 1);
                }
            ])
            ->with([
                'unitDurations' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'unitduration');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'serviceType' => function ($query) use ($language_id) {
                    $query->select(['id', 'code', 'abbreviation']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'servicetype');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'service_translations' => function ($query) use ($language_id) {
                    $query->select('id', 'language_id', 'name', 'name_commercial',
                        'description', 'itinerary', 'summary', 'description_commercial',
                        'itinerary_commercial', 'summary_commercial', 'service_id');
                    $query->where('language_id', $language_id);
                }
            ])->with([
                'children_ages' => function ($query) {
                    $query->select(['service_id', 'min_age', 'max_age'])->where('status', 1);
                }
            ])
            //TODO si se buscan niños se excluyen los servicios que no admintan niños y que permitan la edad minima
            ->when($child_min_age_search != null, function ($query, $child_min_age_search) {
                //campo min_age = edad minima para el servicio
                return $query->where('allow_child', 1);
//                    ->where('min_age', '<=', $child_min_age_search);
            })
            //TODO si se buscan niños se excluyen los servicios que no permitan las edades
            ->when($child_min_age_search != null, function ($query) use ($child_min_age_search) {
                return $query->whereHas('children_ages', function ($query) use ($child_min_age_search) {
                    $query->where('min_age', '<=', $child_min_age_search);
                    $query->where('max_age', '>=', $child_min_age_search);
                });
            });

        $query_search = $query_search->whereIn('id', $services_id)->get();
        return $query_search;

    }

    public function getDayToCharge($date_init, $days_to_charge_config)
    {
        $days_charge = [];
        $days_not_charge = [];
        if (count($days_to_charge_config) > 0) {
            foreach ($days_to_charge_config as $key => $day) {
                if ($key === 0) {
                    $day_to = Carbon::parse($date_init)->format('d/m/Y');
                } else {
                    $day_to = Carbon::parse($date_init)->addDays($key)->format('d/m/Y');
                }
                if ((boolean)$day->charge == true) {
                    $days_charge[] = [
                        'day' => $day_to,
                        'available' => false,
                    ];
                } else {
                    $days_not_charge[] = [
                        'day' => $day_to,
                        'available' => false,
                    ];
                }

            }
        } else {
            $days_charge[] = [
                'day' => Carbon::parse($date_init)->format('d/m/Y'),
            ];
        }

        return [
            'charge' => $days_charge,
            'not_charge' => $days_not_charge,
        ];

    }

    public function getAllDaysSupplement($date_init, $days_to_charge_config)
    {
        $days = [];
        if (count($days_to_charge_config) > 0) {
            foreach ($days_to_charge_config as $key => $day) {
                if ($key === 0) {
                    $day_to = Carbon::parse($date_init)->format('d/m/Y');
                } else {
                    $day_to = Carbon::parse($date_init)->addDays($key)->format('d/m/Y');
                }
                $days[] = $day_to;
            }
        } else {
            $days[] = Carbon::parse($date_init)->format('d/m/Y');
        }

        return $days;

    }

    /**
     * @param integer $adults
     * @param integer $child
     * @param array $dates
     * @param integer $service_id
     * @param integer $supplement_id
     * @param string $token_search
     * @throws \Exception
     */
    public function changeInputsSupplement(
        $quantity_adults,
        $quantity_child,
        $dates,
        $service_id,
        $supplement_id,
        $token_search
    )
    {
        $rate = [
            'price_per_adult' => 0,
            'price_per_child' => 0,
            'total_adult_amount' => 0,
            'total_child_amount' => 0,
            'price_per_person' => 0,
            'total_amount' => 0,
        ];
        $serviceSearch = $this->getTokenSearchService($token_search);

        $selectedService = null;
        $selectedService = collect($serviceSearch)->first(function ($service) use ($service_id) {
            return $service['id'] == $service_id;
        });


        if (!$selectedService) {
            throw new \Exception("Service {$service_id} not found on token {$token_search}");
        }

        $supplementSelected = collect($selectedService['supplements']['optional_supplements'])->first(function (
            $supplement
        ) use ($supplement_id) {
            return $supplement['id'] == $supplement_id;
        });
        $quantity_total_pax = $quantity_adults + $quantity_child;

        //Todo obtengo las tarifas nuevas de las fechas
        $getSupplementRatePlans = $this->getRatePlansSupplement($supplementSelected['id'], $dates,
            1, $quantity_total_pax);

        $pricesCharge = collect();

        //Todo Obtengo los datos del cache del suplemento
        $getSupplementByToken = $this->getTokenSearchService($supplementSelected['token_search']);

        $storeNewCacheSupplement = collect();
        foreach ($dates as $date) {
            $rate_plan_date = $getSupplementRatePlans->first(function ($value) use ($date) {
                return $value['date'] == $date;
            });
            if ($rate_plan_date) {
                $prices_required = $this->calculateAmountSupplement($rate_plan_date['rate']['service_rate_plans'],
                    $quantity_adults, $quantity_child, $selectedService['rate']['markup']);
                $pricesCharge->add($prices_required);
                //Todo Actualizo los datos del cache
                $storeNewCacheSupplement->add([
                    'date' => $date,
                    'supplement' => $getSupplementByToken[0]['supplement'],
                    'adults' => $quantity_adults,
                    'child' => $quantity_child,
                    'rate' => $rate_plan_date['rate'],
                    'total_prices' => $prices_required,
                ]);
            }
        }

        if ($pricesCharge->count() > 0) {
            //Todo Guardo en cache los nuevas tarifas con el mismo numero de cache
            $rate = [
                'price_per_adult' => $pricesCharge->sum('price_per_adult'),
                'price_per_child' => $pricesCharge->sum('price_per_child'),
                'total_adult_amount' => $pricesCharge->sum('total_adult_amount'),
                'total_child_amount' => $pricesCharge->sum('total_child_amount'),
                'price_per_person' => $pricesCharge->sum('price_per_person'),
                'total_amount' => $pricesCharge->sum('total_amount'),
            ];
            Cache::forget($supplementSelected['token_search']);
            $this->storeTokenSearchSupplements($supplementSelected['token_search'], $storeNewCacheSupplement,
                $this->expiration_search_supplement);
//            throw new \Exception(json_encode($this->getTokenSearchService($supplementSelected['token_search'])));
        }
        return $rate;
    }

    public function updateDatesCharge($days_to_charge, $getSupplementRatePlans)
    {
        foreach ($days_to_charge['charge'] as $key => $charge_day) {
            foreach ($getSupplementRatePlans as $day) {
                if ($charge_day['day'] == $day['date']) {
                    $days_to_charge['charge'][$key]['available'] = $day['available'];
                }
            }
        }

        foreach ($days_to_charge['not_charge'] as $key => $charge_day) {
            foreach ($getSupplementRatePlans as $day) {
                if ($charge_day['day'] == $day['date']) {
                    $days_to_charge['not_charge'][$key]['available'] = $day['available'];
                }
            }
        }

        return $days_to_charge;
    }

    public function getRatePlansSupplement($supplement_id, $dates, $language_id = 1, $total_pax = 1)
    {
        $supplement_rate_plans = collect();
        foreach ($dates as $key => $date) {
            $date_to = Carbon::createFromFormat('d/m/Y', $date);
            $rate_plans = $this->getServiceRatePlans($supplement_id, $date_to, $total_pax, $language_id);
            if ($rate_plans) {
                if ($rate_plans->service_rate_plans->count() > 0) {
                    $supplement_rate_plans->add([
                        'date' => $date,
                        'available' => true,
                        'rate' => $rate_plans
                    ]);
                } else {
                    $supplement_rate_plans->add([
                        'date' => $date,
                        'available' => false,
                        'rate' => $rate_plans
                    ]);
                }
            } else {
                $supplement_rate_plans->add([
                    'date' => $date,
                    'available' => false,
                    'rate' => collect()
                ]);
            }

        }
        return $supplement_rate_plans;
    }

    public function getServiceRatePlans($service_id, $date_to, $total_pax, $language_id = 1)
    {
        return ServiceRate::where('service_id', $service_id)->where('status', 1)
            ->whereDoesntHave('clients_rate_plan', function ($query) use ($date_to) {
                $query->where('client_id', $this->client_id());
                $query->where('period', Carbon::parse($date_to)->year);
            })->with([
                'service_rate_plans' => function ($query) use ($date_to, $total_pax) {
                    $query->select([
                        'id',
                        'service_rate_id',
                        'service_cancellation_policy_id',
                        'user_id',
                        'date_from',
                        'date_to',
                        'pax_from',
                        'pax_to',
                        'price_adult',
                        'price_child',
                        'price_infant',
                        'price_guide',
                        'status',
                    ]);
                    $query->where('date_from', '<=', $date_to->format('Y-m-d'))
                        ->where('date_to', '>=', $date_to->format('Y-m-d'));
                    $query->where('pax_from', '<=', $total_pax)
                        ->where('pax_to', '>=', $total_pax)
                        ->where('status', 1);
                    $query->with([
                        'policy' => function ($query) {
                            $query->where('status', 1);
                            $query->with([
                                'parameters' => function ($query) {
                                    $query->with('penalty');
                                }
                            ]);
                        }
                    ]);
                }
            ])->with([
                'translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                }
            ])->first(['id', 'name', 'service_id', 'status']);
    }


    public function storeTokenSearchSupplements($token_search, $supplements, $minutes)
    {
        Cache::put($token_search, $supplements, now()->addMinutes($minutes));
    }

    public function getTokenSearchService($token_search)
    {
        if (empty($this->selected_tokens_search[$token_search])) {
            $servicesSearch = $this->getServicesByTokenSearch($token_search);
            if (!empty($servicesSearch['error'])) {
                throw new \Exception($servicesSearch['error']);
            }
            $this->selected_tokens_search[$token_search] = $servicesSearch;
        }
        return $this->selected_tokens_search[$token_search];
    }

    public function getServicesByTokenSearch($token_search)
    {
        if (Cache::has($token_search)) {
            return Cache::get($token_search);
        } else {
            return ["error" => trans('validations.reservations.your_search_has_expired')];
        }
    }

    public function reformat_data(
        $service,
        $client_id,
        $date_to,
        $dayOfWeek,
        $check_in,
        $current_date,
        $to,
        $quantity_child,
        $quantity_adults,
        $child_min_age,
        $language_id,
        $quantity_persons,
        $supplements_has,
        $quantity_total_pax,
        $setMarkup,
        $token_search,
        $infant_min_age = 0,
        $infant_max_age = 0,
        $countries = []
    )
    {

        $service_ = [];
        $currency_to_change = Currency::whereIso('USD')->first();
        $symbol_to_change = '';
        $iso_to_change = '';
        if ($currency_to_change) {
            $symbol_to_change = $currency_to_change->symbol;
            $iso_to_change = $currency_to_change->iso;
        }

        $service_['id'] = $service['id'];
        $service_['name'] = $service['name'];
        $service_['service_mask'] = $service['service_mask'];
        $service_['alerta_change_children_ages'] = isset($service['alerta_change_children_ages']) ? $service['alerta_change_children_ages'] : false;
        $service_['code'] = strtoupper($service['aurora_code']);
        $service_['coordinates'] = [
            'latitude' => $service['latitude'],
            'longitude' => $service['longitude'],
        ];
        $service_['reserve_from_days'] = $service['qty_reserve'];
        $service_['equivalence'] = $service['equivalence_aurora'];
        $service_['affected_igv'] = $service['affected_igv'];
        $service_['affected_markup'] = $service['affected_markup'];
        $service_['allows_guide'] = $service['allow_guide'];
        $service_['compensation'] = (boolean)$service['compensation'];
        $hasEcommerceService = (!empty($service['client_services']) and count($service['client_services']) > 0) ? true : false;
        $allows_child = 0;
        $children_ages = [
            'min' => 0,
            'max' => 0
        ];
        if ($service['allow_child'] and count($service['children_ages']) > 0) {
            $allows_child = 1;
            $children_ages = [
                'min' => $service['children_ages'][0]['min_age'],
                'max' => $service['children_ages'][0]['max_age']
            ];
        }
        $service_['allows_child'] = $allows_child;
        $service_['children_age_allowed'] = $children_ages;
        $service_['allows_infant'] = $service['allow_infant'];
        $service_['confirmation_hours_limit'] = $service['limit_confirm_hours'];
        $service_['include_accommodation'] = $service['include_accommodation'];
        $service_['unit_of_duration'] = $service['unit_durations']['translations'][0]['value'];
        $service_['tag'] = $service['tag_service'];
        $service_['favorite'] = false;
        $service_['languages_guide'] = $service["languages_guide"];
        $service_['allowed_quantity'] = [
            'min' => $service["pax_min"],
            'max' => $service["pax_max"],
        ];


        //Todo Valoraciones
        $service_['rated'] = (count($service['rated']) > 0) ? $service['rated'][0]['rated'] : 0;

        $symbol = $service['currency']['symbol'];
        $iso = $service['currency']['symbol'];
        if ($service['currency']['iso'] == 'PEN' & $symbol_to_change !== '') {
            //Moneda
            $symbol = $symbol_to_change;
            $iso = $iso_to_change;
        }

        //Todo Modeda
        $service_['currency'] = [
            'symbol' => $symbol,
            'iso' => $iso,
        ];

        //Todo Categoria
        $service_['category'] = [
            'category' => $service['service_sub_category']['service_categories']['translations'][0]['value'],
            'sub_category' => $service['service_sub_category']['translations'][0]['value'],
            'service_category_id' => $service['service_sub_category']['service_category_id']
        ];
        //Todo Duracion del servicio
        $service_['duration'] = $service['duration'];
        $city_origin = (isset($service['service_origin'][0]['city']['translations'][0]['value'])) ? $service['service_origin'][0]['city']['translations'][0]['value'] : null;
        $zone_origin = (isset($service['service_origin'][0]['zone']['translations'][0]['value'])) ? $service['service_origin'][0]['zone']['translations'][0]['value'] : null;
        $service_['origin'] = [
            'origin_display' => '',
            'country' => isset($service['service_origin'][0]) ? $service['service_origin'][0]['country']['translations'][0]['value'] : '',
            'country_iso' => isset($service['service_origin'][0]) ? $service['service_origin'][0]['country']['iso'] : '',
            'state' => isset($service['service_origin'][0]) ? $service['service_origin'][0]['state']['translations'][0]['value'] : '',
            'state_iso' => isset($service['service_origin'][0]) ? $service['service_origin'][0]['state']['iso'] : '',
            'city' => $city_origin,
            'zone' => $zone_origin,
        ];
        // Construye origin_display con solo los campos requeridos
        $service_['origin']['origin_display'] = implode(',', array_filter([
            $service_['origin']['country'],
            $service_['origin']['state'],
            $service_['origin']['city'],
            $service_['origin']['zone'],
        ]));
        $city_destiny = (isset($service['service_destination'][0]['city']['translations'][0]['value'])) ? $service['service_destination'][0]['city']['translations'][0]['value'] : null;
        $zone_destiny = (isset($service['service_destination'][0]['zone']['translations'][0]['value'])) ? $service['service_destination'][0]['zone']['translations'][0]['value'] : null;
        $service_['destiny'] = [
            'country' => isset($service['service_destination'][0]) ? $service['service_destination'][0]['country']['translations'][0]['value'] : '',
            'country_iso' => isset($service['service_destination'][0]) ? $service['service_destination'][0]['country']['iso'] : '',
            'state' => isset($service['service_destination'][0]) ? $service['service_destination'][0]['state']['translations'][0]['value'] : '',
            'state_iso' => isset($service['service_destination'][0]) ? $service['service_destination'][0]['state']['iso'] : '',
            'city' => $city_destiny,
            'zone' => $zone_destiny,
        ];
        $service_['destiny']['destiny_display'] = implode(',', array_filter([
            $service_['destiny']['country'],
            $service_['destiny']['state'],
            $service_['destiny']['city'],
            $service_['destiny']['zone'],
        ]));

        //Todo tipo de servicio
        $service_['service_type'] = [
            'id' => $service['service_type']['id'],
            'name' => $service['service_type']['translations'][0]['value'],
            'code' => $service['service_type']['code'],
        ];

        //Todo clasificacion
        $image_classification = '';
        if (count($service['classification']['galeries']) > 0) {
            $image_classification = verifyCloudinaryImg($service['classification']['galeries'][0]['url'], 500, 450, '');
        }
        $service_['classification'] = [
            'id' => $service['classification']['id'],
            'name' => $service['classification']['translations'][0]['value'],
            'image' => $image_classification
        ];

        $itinerary = [];
        if (!empty($service['service_translations'][0]['itinerary'])) {
            $itinerary = $this->getFormatItinerary($service['service_translations'][0]['itinerary']);
        }

        $service_['available_from'] = [
            'from' => 0,
            'unit_duration' => '',
        ];
        //Todo Configuracion de disponibilidad por cliente
        if (count($service['client_service_setting']) > 0) {
            $service_['available_from'] = [
                'from' => $service['client_service_setting'][0]['reservation_from'],
                'unit_duration' => ($service['client_service_setting'][0]['unit_duration_reserve'] == 2) ? 'days' : 'hours',
            ];
        }

        //Todo Descripcion
        $service_['descriptions'] = [
            'name' => $service['service_translations'][0]['name'],
            'name_gtm' => strtoupper($service['service_translations_gtm'][0]['name']),
            'name_commercial' => $service['service_translations'][0]['name'],
            'description' => $service['service_translations'][0]['description'],
            'itinerary' => $itinerary,
            'summary' => $service['service_translations'][0]['summary'],
        ];

        //Todo Descripcion Comercial
        $service_['commercial_descriptions'] = [
            'name' => $service['service_translations'][0]['name'],
            'name_commercial' => $service['service_translations'][0]['name'],
            'description' => $service['service_translations'][0]['description'],
            'itinerary' => $itinerary,
            'summary' => $service['service_translations'][0]['summary'],
        ];

        $service_['languages_guide'] = [
            'language_display' => '',
            'iso_display' => '',
            'languages' => []
        ];

        if (count($service['languages_guide']) > 0) {
            $languages = [];
            foreach ($service['languages_guide'] as $language) {
                $languages[] = [
                    'id' => $language['language']['id'],
                    'name' => $language['language']['name'],
                    'iso' => $language['language']['iso'],
                ];
            }
            $service_['languages_guide'] = [
                'language_display' => implode(",", array_column($languages, 'name')),
                'iso_display' => implode(",", array_column($languages, 'iso')),
                'languages' => $languages
            ];
        }

        $service_['experiences'] = [];
        //Todo Experiencias
        foreach ($service['experience'] as $experience) {
            $service_['experiences'][] = [
                'id' => $experience['id'],
                'name' => $experience['translations'][0]['value'],
                'color' => $experience['color'],
            ];
        }

        $service_['restrictions'] = [];
        //Todo restricciones
        foreach ($service['restriction'] as $restriction) {
            $service_['restrictions'][] = [
                'id' => $restriction['id'],
                'name' => $restriction['translations'][0]['value'],
            ];
        }

        $service_['galleries'] = [];
        //Todo Galerias
        foreach ($service['galleries'] as $gallery) {
            $service_['galleries'][] = verifyCloudinaryImg($gallery['url'], 400, 450, '');
        }

        $country_iso = $service_['origin']['country_iso'] ?? null;
        $country = collect($countries)->where('iso', $country_iso)->first();
        $regionId = $country['business_region_id'];

        $markup_general = DB::table('markups')
                        ->where('client_id', $client_id)
                        ->where('period',Carbon::parse($date_to)->year)
                        ->where('business_region_id', $regionId)
                        ->where('status',1)
                        ->first();


        if ($setMarkup > 0) { // Todo Verifico primero si tiene un markup asignado de forma obligatoria (Ejm: Cotizador)
            $markup = $setMarkup;
        } elseif (count($service['service_rate']) > 0 and count($service['service_rate'][0]['markup_rate_plan']) > 0) { // Todo Verifico si tiene el markup por tarifa
            $markup = $service['service_rate'][0]['markup_rate_plan'][0]['markup'];
        } elseif (count($service['markup_service']) > 0) { // Todo Verifico si tiene el markup por servicio
            $markup = $service['markup_service'][0]['markup'];
        } else { //Todo Si no tomo el markup general de servicios
            if ($markup_general) {
                $markup = $markup_general->service;
            } else {
                throw new \Exception('The client does not have a markup for the year ' . Carbon::parse($date_to)->year);
            }
        }

        //Todo Si el servicios es un servicio propio del cliente entonces su servicio tiene como markup 0
        if ($hasEcommerceService) {
            $markup = 0;
        }

        //Todo highlights
        $service_['highlights'] = [];
        foreach ($service['highlights'] as $key => $item) {
            $highlight = $item['featured']['translations'][0]['value'];
            $service_['highlights'][] = [
                'highlight' => $highlight
            ];
        }

        //Todo instructions
        $service_['instructions'] = [];
        foreach ($service['instructions'] as $key => $item) {
            $instruction = $item['instructions']['translations'][0]['value'];
            $service_['instructions'][] = [
                'instruction' => $instruction
            ];
        }

        //Todo instructions
        $service_['physical_intensity'] = [];
        if (!empty($service['physical_intensity'])) {
            $service_['physical_intensity'] = [
                'name' => $service['physical_intensity']['translations'][0]['value'],
                'color' => $service['physical_intensity']['color'],
            ];
        }

        $service_['operations']['turns'] = [];
        //Detalle dia a dia de las operaciones

        $key_ = 0;

        foreach ($service['schedules'] as $keyA_ => $itemA_) {

            $operabilities = ServiceOperation::where('service_id', $service_['id'])
                ->where('service_schedule_id', $itemA_['id'])
                ->with([
                    'services_operation_activities.service_type_activities.translations' => function ($query) use (
                        $language_id
                    ) {
                        $query->where('type', 'servicetypeactivity');
                        $query->where('language_id', $language_id);
                    }
                ])->get()->toArray();

            $key = 0;
            foreach ($operabilities as $keyA => $itemA) {
                $start_time = $itemA['start_time'];
                $service_['operations']['turns'][$key_][$key] = [
                    'day' => $itemA['day'],
                    'departure_time' => $itemA['start_time'],
                    'shifts_available' => $itemA['shifts_available'],
                    'detail' => [],
                ];
                foreach ($itemA['services_operation_activities'] as $keyB => $itemB) {
                    if ($keyB > 0) {
                        $count = count($service_['operations']['turns'][$key_][$key]['detail']);
                        $start_time = $service_['operations']['turns'][$key_][$key]['detail'][$count - 1]['end_time'];
                    }
                    $start_end = Carbon::createFromFormat('H:i:s',
                        $start_time)->addMinutes($itemB['minutes'])->toTimeString();

                    $service_['operations']['turns'][$key_][$key]['detail'][] = [
                        'detail' => (count($itemB['service_type_activities']['translations']) == 0) ? '' : $itemB['service_type_activities']['translations'][0]['value'],
                        'start_time' => $start_time,
                        'end_time' => $start_end,
                    ];
                }
                $key++;
            }
            $key_++;
        }


        //Todo Dias de operacion y horarios
        $service_['operations']['days'] = [
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
            'saturday' => false,
            'sunday' => false,
        ];

        $service_['operations']['schedule'] = [];

        $service_['schedules'] = $service['schedules'];

        //Todo Horario de opearcion
        $schedules = $service['schedules'];
        if (count($schedules) == 0) {
            $service_start_time = Carbon::createFromFormat('H:i:s', '00:00:00', 'America/Lima');
        } else {
            $time_week = $schedules[0]['services_schedule_detail'][0][strtolower($dayOfWeek)];
            $time = (is_null($time_week)) ? '00:00:00' : $time_week;
            if (Carbon::parse($check_in)->format('Y-m-d') == Carbon::parse($current_date)->format('Y-m-d')) {
                $service_start_time = Carbon::createFromFormat('H:i:s', $time, 'America/Lima');
            } else {

                $check_in = Carbon::createFromFormat('Y-m-d H:i:s', $date_to . ' ' . '00:00:00',
                    'America/Lima');
                $service_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $date_to . ' ' . $time,
                    'America/Lima');
            }
        }


        foreach ($schedules as $schedule) {
            $monday = ' - ';
            $tuesday = ' - ';
            $wednesday = ' - ';
            $thursday = ' - ';
            $friday = ' - ';
            $saturday = ' - ';
            $sunday = ' - ';
            if (!empty($schedule['services_schedule_detail'][0]['monday']) and $schedule['services_schedule_detail'][0]['monday'] != '00:00:00') {
                $monday = $schedule['services_schedule_detail'][0]['monday'] . ' - ' . $schedule['services_schedule_detail'][1]['monday'];
                $service_['operations']['days']['monday'] = true;
            }
            if (!empty($schedule['services_schedule_detail'][0]['tuesday']) and $schedule['services_schedule_detail'][0]['tuesday'] != '00:00:00') {
                $tuesday = $schedule['services_schedule_detail'][0]['tuesday'] . ' - ' . $schedule['services_schedule_detail'][1]['tuesday'];
                $service_['operations']['days']['tuesday'] = true;
            }
            if (!empty($schedule['services_schedule_detail'][0]['wednesday']) and $schedule['services_schedule_detail'][0]['wednesday'] != '00:00:00') {
                $wednesday = $schedule['services_schedule_detail'][0]['wednesday'] . ' - ' . $schedule['services_schedule_detail'][1]['wednesday'];
                $service_['operations']['days']['wednesday'] = true;
            }
            if (!empty($schedule['services_schedule_detail'][0]['thursday']) and $schedule['services_schedule_detail'][0]['thursday'] != '00:00:00') {
                $thursday = $schedule['services_schedule_detail'][0]['thursday'] . ' - ' . $schedule['services_schedule_detail'][1]['thursday'];
                $service_['operations']['days']['thursday'] = true;
            }
            if (!empty($schedule['services_schedule_detail'][0]['friday']) and $schedule['services_schedule_detail'][0]['friday'] != '00:00:00') {
                $friday = $schedule['services_schedule_detail'][0]['friday'] . ' - ' . $schedule['services_schedule_detail'][1]['friday'];
                $service_['operations']['days']['friday'] = true;
            }
            if (!empty($schedule['services_schedule_detail'][0]['saturday']) and $schedule['services_schedule_detail'][0]['saturday'] != '00:00:00') {
                $saturday = $schedule['services_schedule_detail'][0]['saturday'] . ' - ' . $schedule['services_schedule_detail'][1]['saturday'];
                $service_['operations']['days']['saturday'] = true;
            }
            if (!empty($schedule['services_schedule_detail'][0]['sunday']) and $schedule['services_schedule_detail'][0]['sunday'] != '00:00:00') {
                $sunday = $schedule['services_schedule_detail'][0]['sunday'] . ' - ' . $schedule['services_schedule_detail'][1]['sunday'];
                $service_['operations']['days']['sunday'] = true;
            }

            $service_['operations']['schedule'][] = [
                'monday' => $monday,
                'tuesday' => $tuesday,
                'wednesday' => $wednesday,
                'thursday' => $thursday,
                'friday' => $friday,
                'saturday' => $saturday,
                'sunday' => $sunday
            ];
        }

        if ($service['service_type']['id'] === 1) {
            $week_name = strtolower($to->format('l'));
            $reserve_time = (count($schedules) > 0) ? $schedules[0]['services_schedule_detail'][0][$week_name] : Carbon::now()->format('H:m');
            $service_['reservation_time'] = $reserve_time;
        } elseif ($service['service_type']['id'] === 2) {
            $service_['reservation_time'] = '';
        } else {
            $service_['reservation_time'] = Carbon::now()->format('H:m');
        }

        //Todo Inclusiones
        $inclusions = collect($service['inclusions'])->groupBy('day')->values();
        foreach ($inclusions as $index_day => $inclusion_day) {
            $_day = Carbon::parse($date_to);
            $_date = $_day->addDays($index_day);
            $_day_name = strtolower($_date->englishDayOfWeek);
            foreach ($inclusion_day as $inclusion) {
                //Todo incluye
                if ($inclusion['include']) {
                    $inclusion_available = ($inclusion['inclusions'][$_day_name] == 0) ? false : true;
                    $service_['inclusions'][$inclusion['day']]['include'][] = [
                        'day' => $inclusion['day'],
                        'date' => Carbon::parse($_day)->format('Y-m-d'),
                        'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                        'available_days' => [
                            'available_day' => $inclusion_available,
                            'days' => [
                                'monday' => (boolean)$inclusion['inclusions']['monday'],
                                'tuesday' => (boolean)$inclusion['inclusions']['tuesday'],
                                'wednesday' => (boolean)$inclusion['inclusions']['wednesday'],
                                'thursday' => (boolean)$inclusion['inclusions']['thursday'],
                                'friday' => (boolean)$inclusion['inclusions']['friday'],
                                'saturday' => (boolean)$inclusion['inclusions']['saturday'],
                                'sunday' => (boolean)$inclusion['inclusions']['sunday'],
                            ]
                        ],
                    ];
                }

                //Todo no incluye
                if (!$inclusion['include']) {
                    $service_['inclusions'][$inclusion['day']]['no_include'][] = [
                        'day' => $inclusion['day'],
                        'date' => Carbon::parse($_day)->format('Y-m-d'),
                        'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                    ];
                }

            }
        }

        if (count($service['inclusions']) > 0) {
            $service_['inclusions'] = array_values($service_['inclusions']);
        } else {
            $service_['inclusions'] = [];
        }

        $price_per_person_adult = 0;
        $price_per_person_child = 0;
        $price_per_person_infant = 0;
        $pax_amounts = isset($service['service_rate'][0]) ? $service['service_rate'][0]['service_rate_plans'] : [];

        foreach ($pax_amounts as $pax_amount) {
            if ($service['affected_markup'] == 1) {
                $price_per_person_adult += ($pax_amount['price_adult'] * 1) + (($pax_amount['price_adult'] * 1) * ($markup / 100));
                if ($allows_child and $quantity_child > 0) {
                    $price_per_person_child += ($pax_amount['price_child'] * 1) + (($pax_amount['price_child'] * 1) * ($markup / 100));
                    $price_per_person_infant += ($pax_amount['price_infant'] * 1) + (($pax_amount['price_infant'] * 1) * ($markup / 100));
                }
            } else {
                $price_per_person_adult += ($pax_amount['price_adult'] * 1);
                if ($allows_child and $quantity_child > 0) {
                    $price_per_person_child += ($pax_amount['price_child'] * 1);
                    $price_per_person_infant += ($pax_amount['price_infant'] * 1);
                }
            }

        }


        $offer_value = 0;

        $price_per_adult = (float)roundLito($price_per_person_adult);
        $price_per_child = (float)roundLito($price_per_person_child);
        $price_per_infant = (float)roundLito($price_per_person_infant);
        $price_adult = $price_per_adult * $quantity_adults;
        $price_child = 0;
        // $price_child = $price_per_child * $quantity_child;

        // hacemos el calculo del precio del niño basandonos en sus edades
        if ($allows_child and $quantity_child > 0) {
            foreach ($quantity_persons["age_childs"] as $age_childs) {

                if ($age_childs['age'] >= $infant_min_age and $age_childs['age'] <= $infant_max_age) {
                    $price_child = $price_child + $price_per_person_infant;
                } elseif ($age_childs['age'] >= $children_ages['min'] and $age_childs['age'] <= $children_ages['max']) {
                    $price_child = $price_child + $price_per_child;
                } else {
                    $price_child = $price_child + $price_per_adult;
                }
            }

        }
        $total_base = $price_adult + $price_child;


        if (isset($service['service_rate'][0]) and count($service['service_rate'][0]['offers']) > 0) {
            $value = $service['service_rate'][0]['offers'][0]['value'];
            $offer_value = $value;
            if ($service['service_rate'][0]['offers'][0]['is_offer']) {
                $service_['offer'] = true;
                $price_per_person_adult = $price_per_person_adult - ($price_per_person_adult * ($value / 100));
                $price_per_person_child = $price_per_person_child - ($price_per_person_child * ($value / 100));
                $price_per_person_infant = $price_per_person_infant - ($price_per_person_infant * ($value / 100));
            } else {
                $service_['offer'] = false;
                $price_per_person_adult = $price_per_person_adult + ($price_per_person_adult * ($value / 100));
                $price_per_person_child = $price_per_person_child + ($price_per_person_child * ($value / 100));
                $price_per_person_infant = $price_per_person_infant + ($price_per_person_infant * ($value / 100));
            }
        } else {
            $service_['offer'] = false;
        }


        $supplements = $this->getSupplementsService($service['supplements'],
            $to, $child_min_age, $language_id, $dayOfWeek, $quantity_adults, $quantity_child,
            $quantity_persons["age_childs"], $children_ages, $service['unit_durations'],
            $service['duration'], $markup, $supplements_has);

        $service_['supplements'] = $supplements;

        //Todo variable para saber si tiene ofertas el servicios
        $service_['offer_value'] = $offer_value;

        $price_per_adult = (float)roundLito($price_per_person_adult);
        $price_per_child = (float)roundLito($price_per_person_child);
        $price_per_infant = (float)roundLito($price_per_person_infant);
        $price_adult = $price_per_adult * $quantity_adults;
        $price_child = 0;

        $import_childres = [];
        // hacemos el calculo del precio del niño basandonos en sus edades
        if ($allows_child and $quantity_child > 0) {
            foreach ($quantity_persons["age_childs"] as $age_childs) {

                if ($age_childs['age'] >= $infant_min_age and $age_childs['age'] <= $infant_max_age) {
                    $price_child = $price_child + $price_per_person_infant;
                    array_push($import_childres, [
                        'age' => $age_childs['age'],
                        'price' => $price_per_person_infant,
                    ]);
                } elseif ($age_childs['age'] >= $children_ages['min'] and $age_childs['age'] <= $children_ages['max']) {
                    $price_child = $price_child + $price_per_child;
                    array_push($import_childres, [
                        'age' => $age_childs['age'],
                        'price' => $price_per_child,
                    ]);
                } else {
                    $price_child = $price_child + $price_per_adult;
                    array_push($import_childres, [
                        'age' => $age_childs['age'],
                        'price' => $price_per_adult,
                    ]);
                }
            }

        }

        // dd($quantity_persons["age_childs"], $price_adult, $price_child);

        //Todo sumamos el total de adulto + niños + los suplementos
        $total_amount_calculated = $price_adult + $price_child;

        //Todo dividomos la cantidad de pax entre el total para scar el precio por persona
        $price_per_person = (float)roundLito($total_amount_calculated / ($quantity_adults + $quantity_child));


        if ($price_adult == null) {
            $price_adult = (float)0;
        }

        if ($price_child == null) {
            $price_child = (float)0;
        }
        $total_amount = $total_amount_calculated;


        // dd( $quantity_child,$quantity_adults,$child_min_age,$markup, $price_per_person_adult, $price_per_person_child );


//                if ($service['currency']['iso'] == 'PEN' & $symbol_to_change !== '') {
//                    $total_amount = $this->convert_currency($total_amount_calculated, $exchange_rate);
//                    $price_adult = $this->convert_currency($price_adult, $exchange_rate);
//                    $price_child = $this->convert_currency($price_child, $exchange_rate);
//                } else {
//                    $total_amount = $total_amount_calculated;
//                }


        //Todo Obtenemos la politica y su penalidad
        $service_rate_plans = [];
        $service_rate_plans_bd = isset($service['service_rate'][0]) ? $service['service_rate'][0]['service_rate_plans'] : [];
        foreach ($service_rate_plans_bd as $rate_plan) {
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

            $political_penalties = $this->calculateCancellationPoliciesServices($current_date, $check_in,
                $total_amount, collect($details_params_cancelations), $quantity_total_pax,
                $service_start_time);

            $translation_political= null;
            if(isset($rate_plan['service_cancellation_policy_id']) and $rate_plan['service_cancellation_policy_id'] != null) {
                $translation_political = DB::table('translations')
                    ->where('type', 'service_cancellation_policies')
                    ->where('object_id', $rate_plan['service_cancellation_policy_id'])
                    ->where('language_id', 1)
                    ->first();
            }

            $politicals = [
                'id' => $rate_plan['policy']['id'],
                'name' => $rate_plan['policy']['name'],
                'cancellation' => [
                    'parameters' => $details_params_cancelations,
                    'penalties' => $political_penalties['penalties']
                ],
                'description' => $translation_political,
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
                'flag_migrate' => (int)$rate_plan['flag_migrate'],
                'political' => $politicals,
            ];
        }
        $on_request = 1; // Todo 1 = RQ;
        $inventory_id = null;
        //Todo verificamos si tiene inventario si lo tiene lo ponemos en 0 = OK
        if (isset($service['service_rate'][0]['inventory']) and count($service['service_rate'][0]['inventory']) > 0 and
            $service['service_rate'][0]['inventory'][0]['inventory_num'] >= $quantity_total_pax
            and $service['service_rate'][0]['inventory'][0]['locked'] == 0) {
            $on_request = 0;
            $inventory_id = $service['service_rate'][0]['inventory'][0]['id'];
        }

        if (count($service_rate_plans) == 0) {
            $service_rate_plans = [
                [
                    "id" => rand(11111, 99999) . substr(round(microtime(true) * 1000), 6),
                    "service_cancellation_policy_id" => null,
                    "date_from" => "",
                    "date_to" => "",
                    "pax_from" => 2,
                    "pax_to" => 2,
                    "price_adult" => "0.00",
                    "price_child" => "0.00",
                    "price_infant" => "0.00",
                    "price_guide" => "0.00",
                    "flag_migrate" => 0,
                    "political" => []
                ]
            ];
        }

        //Todo Armamos la tarifa
        $service_['rate'] = [
            'id' => isset($service['service_rate'][0]) ? $service['service_rate'][0]['id'] : '',
            'name' => isset($service['service_rate'][0]) ? $service['service_rate'][0]['translations'][0]['value'] : '',
            'markup' => (double)$markup,
            'rate_plans' => $service_rate_plans,
            'inventory_id' => $inventory_id,
        ];

        $total_taxes = 0;
        $sub_total = $total_amount;

        if ($service['affected_igv'] == 1 and count($service['tax']) > 0) {
            $tax = (double)$service['tax'][0]['amount'];

            $total_taxes = ($total_amount * ($tax / 100));
            $total = $total_amount + $total_taxes;
            $total_amount = $total;
        }

        if (Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 3) {
            $service_['notes'] = $service['notes'];
        }

        $service_['price_per_person'] = $price_per_person;
        $service_['price_per_adult'] = $price_per_adult;
        $service_['price_per_child'] = $price_per_child;
        $service_['price_per_infant'] = $price_per_infant;
        $service_['import_childres'] = $import_childres;
        $service_['quantity_adult'] = $quantity_adults;
        $service_['quantity_child'] = $quantity_child;
        $service_['total_amount_adult'] = $price_adult;
        $service_['total_amount_child'] = $price_child;
        $service_['affected_igv'] = $service['affected_igv'];
        $service_['sub_total'] = $sub_total;
        $service_['total_taxes'] = $total_taxes;
        $service_['total_amount'] = $total_amount;
        $service_['total_base_amount'] = $total_base;
        $service_['base_pax'] = ($quantity_adults + $quantity_child);
        $service_['cart_items_id'] = '';
        $service_['taken'] = false;
        $service_['date_reserve'] = $date_to;
        $service_['on_request'] = $on_request;
        $service_['token_search'] = $token_search;

        return $service_;

    }

    public function get_hour_ini($schedule_id, $date_in, $service_id)
    {

        $hour = null;

        if ($schedule_id != null && $schedule_id != "") {
            $schedule = ServiceSchedule::with(['servicesScheduleDetail'])->find($schedule_id);
            if ($schedule) {
                $date_in = Carbon::parse($date_in);
                $week_name = strtolower($date_in->format('l'));
                $schedule = $schedule->toArray();
                $hour = $schedule['services_schedule_detail'][0][$week_name];
                $hour = ($hour === '') ? null : $hour;
            }
        }

        if ($hour === null && $service_id !== null) {
            $schedule = ServiceSchedule::with(['servicesScheduleDetail'])
                ->where('service_id', $service_id)
                ->first();
            if ($schedule) {
                $date_in = Carbon::parse($date_in);
                $week_name = strtolower($date_in->format('l'));
                $schedule = $schedule->toArray();
                $hour = $schedule['services_schedule_detail'][0][$week_name];
                $hour = ($hour === '') ? null : $hour;
            }
        }

        return $hour;
    }

}
