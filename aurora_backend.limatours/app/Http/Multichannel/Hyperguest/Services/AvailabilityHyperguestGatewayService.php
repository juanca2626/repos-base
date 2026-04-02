<?php

namespace App\Http\Multichannel\Hyperguest\Services;

use App\ChannelHotel;
use App\Language;
use App\City;
use App\Http\Aurora\Hotels\Traits\AvailabilityAuroraHotelTrait;
use App\Http\Multichannel\Hyperguest\Traits\HyperguestGatewayTrait;
use App\Http\Requests\HotelSearchRequest;
use App\Http\Services\Traits\ClientHotelOnRequestTrait;
use App\Http\Services\Traits\ClientTrait;
use App\Http\Services\Traits\ServiceControllerTrait;
use App\Http\Traits\AddHotelRateTaxesAndServices;
use App\Http\Traits\CalculateCancellationlPolicies;
use App\Http\Traits\CalculateHotelTxesAndServices;
use App\Http\Traits\Currencies;
use App\Http\Traits\Hotels;
use App\Http\Traits\HotelsAvailByDestination;
use App\Http\Traits\Images;
use App\Http\Traits\ManageSearchHotel;
use App\Http\Traits\Package;
use App\Http\Traits\Services;
use App\Markup;
use App\State;
use App\Zone;
use Carbon\Carbon;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class AvailabilityHyperguestGatewayService
{
    use HyperguestGatewayTrait;
    use AvailabilityAuroraHotelTrait;
    use ManageSearchHotel;
    use CalculateCancellationlPolicies;
    use CalculateHotelTxesAndServices;
    use HotelsAvailByDestination;
    use ClientTrait;
    use Services;
    use Package;
    use Images;
    use Currencies;
    use Hotels;
    use AddHotelRateTaxesAndServices;
    use ServiceControllerTrait;
    use ClientHotelOnRequestTrait;

    public $expiration_search_hotels = 180; // 3 horas

    // Form request hoteles
    public function hotels(HotelSearchRequest $request, int $client_id): array
    {
        // Extraemos los parámetros de búsqueda
        $params = $this->getParamsFromRequest($request);

        [
            'hotels_id' => $hotels_id,
            'hotels_search_code' => $hotels_search_code,
            'destiny' => $destiny,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'typeclass_id' => $typeclass,
            'setMarkup' => $setMarkup,
            'language_id' => $language_id,
            'lang_iso' => $lang_iso,
        ] = $params;

        if ($hotels_search_code) {
            // VALIDA QUE SEA DE AURORA - Busca por código o por nombre del hotel usando LIKE
            $searchTerm = trim($hotels_search_code);
            $hotels_id = ChannelHotel::where('channel_hotel.channel_id', 1)
                ->leftJoin('hotels', 'channel_hotel.hotel_id', '=', 'hotels.id')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('channel_hotel.code', $searchTerm)
                        ->orWhere('hotels.name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->pluck('channel_hotel.hotel_id')
                ->toArray();
        }

        if ($hotels_id and is_array($hotels_id)) {
            $channelHotel = ChannelHotel::whereIn('hotel_id', $hotels_id)
                ->where('channel_id', 6)
                ->where('type', 2)
                ->pluck('code')
                ->toArray();

            if (!empty($channelHotel)) {
                $hotels_search_code = implode(',', $channelHotel);
                $request->merge(['hotels_search_code' => $hotels_search_code]);
            }else {
                // PARA LOS HOTELES QUE NO ESTAN EN HYPERGUEST
                return [
                    'success' => true,
                    'data'    => [
                        [
                            "city" => [
                                "class"     => [],
                                "zones"     => [],
                                "hotels"    => [],
                            ]
                        ]
                    ]
                ];
            }
        }

        App::setLocale($lang_iso);

        // === Ubicación y fechas base ===
        list($country_id, $state_id, $city_id, $district_id) = $this->getDestinationLocationCodes($destiny['code'] ?? '', $destiny['label'] ?? '');

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);
        $period = $from->year;

        // Establecer cliente (una sola vez)
        $this->setClient($client_id);
        if (empty($this->_client)) {
            return [
                'success' => false,
                'error' => trans('validations.quotes.client_not_found'),
                'status_code' => 404,
            ];
        }

        if (!$setMarkup) {

            $client_markup = Markup::whereHas('businessRegion.countries', function ($query) use ($country_id) {
                $query->where('iso', $country_id);
            })->where(['client_id' => $client_id, 'period' => $period])->first();

            if (!$client_markup) {
                return [
                    'success' => false,
                    'error' => trans('validations.quotes.client_does_not_have_markup_for_year', ['year' => $period]),
                    'status_code' => 404,
                ];
            }
        }

        // Filtro por clase de hotel (más conciso)
        $typeclass_id = ($typeclass !== "all" && $typeclass !== "hotel_id") ? $typeclass : "";

        try {
            // Recalcular métricas derivadas con las fechas ya parseadas arriba
            $reservation_days = $from->diffInDays($to);

            // Rango Y-m-d para downstream
            $fromStr = $from->format('Y-m-d');

            // Agregar validacion que el to debe de ser mayor o igual que el from
            $toStr = $to->copy()->subDay(1)->format('Y-m-d');

            /* TODO: ALEX QUISPE */
            $extraRequest = [
                'client_id' => $client_id,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'from' => $fromStr,
                'to' => $toStr,
                "country_id" => $country_id,
                "state_id" => $state_id,
                "city_id" => $city_id,
                "district_id" => $district_id,
                "reservation_days" => $reservation_days,
                "language_id" => $language_id,
                "typeclass_id" => $typeclass_id,
                "period" => $period,
                "hotels_search_code" => $hotels_search_code
            ];
            $clientHotels = $this->searchAvailabilityLegacy($request, $extraRequest);

            // === Mejores Opciones por hotel (inicialización) ===
            foreach ($clientHotels as $hotelIndex => $clientHotel) {
                $clientHotels[$hotelIndex]['best_options'] = [
                    "quantity_rooms" => 0,
                    "quantity_adults" => 59,
                    "quantity_child" => 0,
                    "total_taxes_and_services_amount" => 0,
                    "total_supplements_amount" => 0,
                    "total_sub_rate_amount" => 0,
                    "total_rate_amount" => 0,
                    "rooms" => [],
                ];
            }

            // Generar token único
            $faker = Faker::create();
            $token_search = $faker->unique()->uuid;

            // Almacenar token de búsqueda para Backend
            $this->storeTokenSearchHotels($token_search, $clientHotels, $this->expiration_search_hotels);

            // Estructurar para el frontend
            $params['token_search_backend'] = $token_search;
            $hotels = $this->sanitizeFrontend($params, $clientHotels, $client_id);

            // Respuesta JSON
            $response = [
                'success' => true,
                'data' => $hotels,
                'expiration_token' => $this->expiration_search_hotels,
            ];

            Cache::put('response_', $response, 3600);

            return $response;
        } catch (Exception $e) {
            app('sentry')->captureException($e); // Capturando error en sentry..
            $statusCode = ($e->getCode() && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return [
                'success' => false,
                'status_code' => $statusCode ?? 500,
                'error' => $e->getTrace(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ];
        }
    }

    // Recalcular disponibilidad y tarifas de una habitación
    public function calculate_selection_rate_total_amount(Request $request, int $client_id): array
    {
        $token_search = $request->input('token_search');
        $hotel_id = $request->input('hotel_id');
        $room_id = $request->input('room_id');
        $selected_rooms = $request->input('rooms');
        $rate_plan_id = $request->input('rate_plan_id');
        $date_to = $request->input('date_to');
        $date_from = $request->input('date_from');
        $destiny = $request->input('destiny');
        $lang_iso = $request->input('language_id') ?? 'es';
        $typeclass_id = $request->input('typeclass_id');
        $ages_child = $request->input('ages_child');
        $quantity_persons_rooms = $request->input('quantity_persons_rooms');


        $params = [
            'destiny' => $destiny,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'typeclass_id' => $typeclass_id,
            'quantity_persons_rooms' => $quantity_persons_rooms,
            'language' => $lang_iso,
            'language_id' => null,
            'token_search_backend' => $token_search,
        ];

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);
        $period = $from->year;

        try {
            // === ID Language ===
            $language = Language::where('iso', $lang_iso)->where('state', 1)->first();
            if (!$language) {
                $lang_iso = 'en';
                $language = Language::where('iso', 'en')->where('state', 1)->first();
            }
            $language_id = $language->id;

            $params['language_id'] = $language_id;

            // === Ubicación y fechas base ===
            list($country_id, $state_id, $city_id, $district_id) = $this->getDestinationLocationCodes($destiny['code'] ?? '', $destiny['label'] ?? '');

            // Recalcular métricas derivadas con las fechas ya parseadas arriba
            $reservation_days = $from->diffInDays($to);

            // Rango Y-m-d para downstream
            $fromStr = $from->format('Y-m-d');

            // Agregar validacion que el to debe de ser mayor o igual que el from
            $toStr = $to->copy()->subDay(1)->format('Y-m-d');

            $this->setClient($client_id);

            $hotel_code = ChannelHotel::where('channel_id', 6)
                ->where('type', 2)
                ->where('hotel_id', trim($hotel_id))
                ->pluck('code')->first();

            $request->merge([
                'hotels_search_code' => $hotel_code,
                'quantity_persons_rooms' => collect($selected_rooms)->map(function ($item) use ($ages_child) {
                    return [
                        'room' => 1,
                        'adults' => $item['quantity_adults'],
                        'child' => $item['quantity_child'],
                        'ages_child' => $item['ages_child'] ?? $ages_child,
                    ];
                })->toArray(),
            ]);

            /* TODO: ALEX QUISPE */
            $extraRequest = [
                'client_id' => $client_id,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'from' => $fromStr,
                'to' => $toStr,
                "country_id" => $country_id,
                "state_id" => $state_id,
                "city_id" => $city_id,
                "district_id" => $district_id,
                "reservation_days" => $reservation_days,
                "language_id" => $language_id,
                "typeclass_id" => $typeclass_id,
                "period" => $period,
                "hotels_search_code" => $hotel_code
            ];
            $lastClientHotels = $this->getHotelsByTokenSearch($token_search);
            $newClientHotels = $this->searchAvailabilityLegacy($request, $extraRequest);

            if (isset($lastClientHotels['error_code'])) {
                throw new Exception($lastClientHotels['error']);
            }

            $clientHotels = $this->mergeClientHotels($lastClientHotels, $newClientHotels, $room_id);
            /* TODO: ALEX QUISPE */

            $result = [];
            $rate_plan_rooms = [];

            if (count($clientHotels) == 0 || array_key_exists("error", $clientHotels)) {
                throw new Exception("La habitación seleccionada no está disponible.");
            }

            foreach ($clientHotels as $hotel) {
                if ($hotel["hotel"]["id"] != $hotel_id)
                    continue;

                $rooms = $hotel["hotel"]["rooms"];
                foreach ($rooms as $room) {
                    if(!isset($room['token_search_channel']))
                        continue;

                    if ($room["id"] !== $room_id)
                        continue;

                    foreach ($room["rates"] as $rate_plan_room) {
                        if ($rate_plan_room["ratePlanId"] !== $rate_plan_id)
                            continue;

                        foreach ($selected_rooms as $sel) {
                            $ad = $sel['quantity_adults'] ?? 0;
                            $ch = $sel['quantity_child'] ?? 0;
                            $ages = $sel['ages_child'] ?? $ages_child;

                            $roomTemp = array_merge(
                                $room,
                                [
                                    'rateId' => $rate_plan_room['rateId'],
                                    'num_adult' => $ad,
                                    'num_child' => $ch,
                                    'quantity_adults_total' => $ad,
                                    'quantity_adults' => $ad,
                                    'quantity_child' => $ch,
                                    'ages_child' => $ages,
                                    'quantity_extras' => 0,
                                    'total_amount' => $rate_plan_room['rate'][0]['total_amount'] ?? 0,
                                    'total_taxes_and_services' => 0,
                                    'calendarys' => $rate_plan_room['calendarys'] ?? [],
                                    'supplements' => ['total_amount' => 0],
                                    'policy_cancellation' => $room['policies_cancelation'] ?? [],
                                    'policies_cancellation' => $room['policies_cancelation'] ?? [],
                                    'translations' => $room['translations'] ?? [],
                                    'channel_id' => $rate_plan_room['channel_id'],
                                    'channel_type' => $rate_plan_room['channel_type'],
                                ]
                            );

                            $rate_plan_rooms[] = $roomTemp;
                        }

                        $result = [
                            'rate_plan_rooms' => $rate_plan_rooms,
                            'political' => [
                                'cancellation' => [
                                    "name" => "",
                                    "details" => [],
                                ],
                            ],
                        ];
                    }
                }
            }

            if (!isset($result['rate_plan_rooms'])) {
                throw new Exception("La habitación seleccionada no está disponible.");
            }

            // Almacenar token de búsqueda para Backend
            $this->storeTokenSearchHotels($token_search, $clientHotels, $this->expiration_search_hotels);

            // Almacenar token de búsqueda para Frontend
            // === Mejores Opciones por hotel (inicialización) ===
            foreach ($clientHotels as $hotelIndex => $clientHotel) {
                $clientHotels[$hotelIndex]['best_options'] = [
                    "quantity_rooms" => 0,
                    "quantity_adults" => 59,
                    "quantity_child" => 0,
                    "total_taxes_and_services_amount" => 0,
                    "total_supplements_amount" => 0,
                    "total_sub_rate_amount" => 0,
                    "total_rate_amount" => 0,
                    "rooms" => [],
                ];
            }
            $this->sanitizeFrontend($params, $clientHotels, $client_id);

            return array_merge(['success' => true], $result);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Sanitizar datos para el frontend
    public function sanitizeFrontend(array $params, array $clientHotels, int $client_id): array
    {
        [
            'destiny' => $destiny,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'typeclass_id' => $typeclass,
            'quantity_persons_rooms' => $quantity_persons_rooms,
            // 'promotional_rate' => $promotional_rate,
            'language' => $language,
            'language_id' => $language_id,
            'token_search_backend' => $token_search,
        ] = $params;

        // === Ubicación y fechas base ===
        list($country_id, $state_id, $city_id, $district_id) = $this->getDestinationLocationCodes($destiny['code'] ?? '', $destiny['label'] ?? '');

        // Parámetros de búsqueda que viajarán en el response
        $search_parameters = [
            'client_id' => $client_id,
            'destiny' => $destiny,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'typeclass_id' => $typeclass,
            'quantity_persons_rooms' => $quantity_persons_rooms,
            // 'promotional_rate' => $promotional_rate,
        ];

        // Estructura base del JSON de respuesta
        $hotelCities = [
            "city" => [
                "token_search" => $token_search,
                "token_search_frontend" => null,
                "ids" => $destiny["code"] ?? '',
                "description" => $destiny["label"] ?? '',
                "class" => [],
                "zones" => [],
                "hotels" => [],
                "search_parameters" => $search_parameters,
                "quantity_hotels" => 0,
            ],
        ];
        $hotels = [$hotelCities];

        $min_price_search = 0;
        $max_price_search = 0;

        $dataHotels = [];

        // Recorrer hoteles
        foreach ($clientHotels as $index => $clientHotel) {
            $hotel_description = $hotel_address = $hotel_logo = $hotel_summary = $hotel_notes = "";
            $amenities = [];
            $hotel_gallery = [];

            // === Zonas ===
            if (!empty($city_id)) {
                $zones = Zone::where('city_id', $city_id)
                    ->with([
                        'translations' => function ($q) use ($language) {
                            $q->where('language_id', $language->id);
                        }
                    ])->get();

                foreach ($zones as $zone) {
                    $zoneName = $zone["translations"][0]["value"];
                    $existing = array_column($hotels[0]["city"]["zones"], 'zone_name');
                    if (!in_array($zoneName, $existing, true)) {
                        $hotels[0]["city"]["zones"][] = ["zone_name" => $zoneName, "status" => false];
                    }
                }
            } elseif (!empty($state_id)) {
                $state = State::where('iso', $state_id)->first();

                if (!$state) {
                    $state = State::find($state_id); // para la busqueda por ID
                }

                $cities = City::where('state_id', $state->id)->get();

                foreach ($cities as $city) {
                    $zones = Zone::where('city_id', $city["id"])
                        ->with([
                            'translations' => function ($q) use ($language) {
                                $q->where('language_id', $language->id);
                            }
                        ])->get();

                    foreach ($zones as $zone) {
                        $zoneName = $zone["translations"][0]["value"];
                        $existing = array_column($hotels[0]["city"]["zones"], 'zone_name');
                        if (!in_array($zoneName, $existing, true)) {
                            $hotels[0]["city"]["zones"][] = ["zone_name" => $zoneName, "status" => false];
                        }
                    }
                }
            }

            // === Clases de hotel ===
            $hoteltypeclass = $this->getHotelTypeClass($clientHotel["hotel"]["hoteltypeclass"]);
            $className = $hoteltypeclass['name'];
            $existingClasses = array_column($hotels[0]["city"]["class"], 'class_name');

            if (!in_array($className, $existingClasses, true)) {
                $hotels[0]["city"]["class"][] = ["class_name" => $className, "status" => false];
            }

            // === Min/Max precio ===
            if (count($clientHotel["best_options"]) > 0) {
                if ($index === 0) {
                    $min_price_search = $max_price_search = $clientHotel["best_options"]["total_rate_amount"];
                } else {
                    $min_price_search = min($min_price_search, $clientHotel["best_options"]["total_rate_amount"]);
                    $max_price_search = max($max_price_search, $clientHotel["best_options"]["total_rate_amount"]);
                }
            }

            // Alerts -> summary/notes (igual que original, solo ordenado)
            if (!empty($clientHotel['hotel']['alerts'])) {
                $alerts = $clientHotel['hotel']['alerts'];
                $hotel_notes = $alerts[0]['remarks'] ?? null;
                $hotel_summary = $alerts[0]['notes'] ?? null;
                if ($language_id != 1 && isset($alerts[1]['notes'])) {
                    $hotel_summary = $alerts[1]['notes'];
                }
            }

            // Traducciones de hotel
            foreach ($clientHotel["hotel"]["translations"] as $translation) {
                if ($translation["slug"] === "hotel_address") {
                    $hotel_address = $translation["value"];
                }
                if ($translation["slug"] === "hotel_description") {
                    $hotel_description = $translation["value"];
                }
            }

            // Galería de hotel
            foreach ($clientHotel["hotel"]["galeries"] as $image) {
                if ($image["slug"] === "hotel_logo") {
                    $hotel_logo = secure_url('/') . '/images/' . $image["url"];
                }
                if ($image["slug"] === "hotel_gallery") {
                    $find_cloudinary = strpos($image["url"], "cloudinary");
                    $hyperguest_image = strpos($image["url"], "hg-static.hyperguest.com");
                    if ($hyperguest_image !== false) {
                        $hotel_gallery[] = $image["url"];
                    } else {
                        $hotel_gallery[] = !$find_cloudinary ? url('/') . '/images/' . $image["url"] : $image["url"];
                    }
                }
            }

            // Amenities
            if (!empty($clientHotel["hotel"]["amenity"])) {
                foreach ($clientHotel["hotel"]["amenity"] as $amenity) {
                    $amenities[] = [
                        "name" => $amenity["translations"][0]["value"],
                        "image" => !empty($amenity["galeries"]) ? secure_url('/') . '/images/' . $amenity["galeries"][0]["url"] : '',
                    ];
                }
            }

            $code_ = ChannelHotel::where("hotel_id", $clientHotel["hotel"]["id"])
                ->where("channel_id", 1)->first();

            $country_name = $country_iso = $state_name = $state_iso = $city_name = $zone_name = $district_name = $hoteltype_name = '';

            if (!empty($clientHotel["hotel"]["country"]) && count($clientHotel["hotel"]["country"]["translations"]) > 0) {
                $country_name = $clientHotel["hotel"]["country"]["translations"][0]["value"];
                $country_iso = $clientHotel["hotel"]["country"]["iso"];
            }
            if (!empty($clientHotel["hotel"]["state"]) && count($clientHotel["hotel"]["state"]["translations"]) > 0) {
                $state_name = $clientHotel["hotel"]["state"]["translations"][0]["value"];
                $state_iso = $clientHotel["hotel"]["state"]["iso"];
            }
            if (!empty($clientHotel["hotel"]["city"]) && count($clientHotel["hotel"]["city"]["translations"]) > 0) {
                $city_name = $clientHotel["hotel"]["city"]["translations"][0]["value"];
            }
            if (!empty($clientHotel["hotel"]["zone"]) && count($clientHotel["hotel"]["zone"]["translations"]) > 0) {
                $zone_name = $clientHotel["hotel"]["zone"]["translations"][0]["value"];
            }
            if (!empty($clientHotel["hotel"]["district"]) && count($clientHotel["hotel"]["district"]["translations"]) > 0) {
                $district_name = $clientHotel["hotel"]["district"]["translations"][0]["value"];
            }
            if (!empty($clientHotel["hotel"]["hoteltype"]) && count($clientHotel["hotel"]["hoteltype"]["translations"]) > 0) {
                $hoteltype_name = $clientHotel["hotel"]["hoteltype"]["translations"][0]["value"];
            }

            $hoteltypeclass = $this->getHotelTypeClass($clientHotel["hotel"]["hoteltypeclass"], $language_id);
            $typeclass_name = $hoteltypeclass['name'];
            $typeclass_color = ""; // $hoteltypeclass['color'];
            $typeclass_order = ""; //$hoteltypeclass['order'];


            $hotel = $clientHotel["hotel"];

            $hotel_description = $hotel["description"] ?? "";
            $hotel_notes = $hotel["notes"] ?? "";

            $hotel = [
                "id" => $hotel["id"],
                "code" => $code_ ? $code_->code : $hotel["id"],
                "flag_new" => $hotel["flag_new"],
                "date_end_flag_new" => $hotel["date_end_flag_new"],
                "name" => $hotel["name"],
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
                "notes" => $hotel_notes,
                "chain" => $hotel["chain"]["name"],
                "logo" => $hotel_logo,
                "category" => (int) ($hotel["stars"]),
                "type" => $hoteltype_name,
                "class" => $typeclass_name,
                "hoteltypeclass" => $hotel["hoteltypeclass"],
                "color_class" => $typeclass_color,
                "order_class" => $typeclass_order,
                "price" => $hotel['price'],
                "coordinates" => [
                    'latitude' => $hotel["latitude"],
                    'longitude' => $hotel["longitude"],
                ],
                "popularity" => count($hotel["hotelpreferentials"]) > 0 ? $hotel["hotelpreferentials"][0]['value'] : 0, // $hotel["preferential"],
                "favorite" => $this->checkHotelFavorite($hotel["id"]),
                "checkIn" => $hotel["check_in_time"],
                "checkOut" => $hotel["check_out_time"],
                "political_children" => [
                    "child" => [
                        "allows_child" => $hotel["allows_child"],
                        "min_age_child" => $hotel["min_age_child"],
                        "max_age_child" => $hotel["max_age_child"],
                    ],
                    "infant" => [
                        "allows_teenagers" => $hotel["allows_teenagers"],
                        "min_age_teenagers" => $hotel["min_age_teenagers"],
                        "max_age_teenagers" => $hotel["max_age_teenagers"],
                    ],
                ],
                "amenities" => $amenities,
                "galleries" => $hotel_gallery,
                "best_options" => [], // $best_options,
                "rooms" => $hotel["rooms"],
                'rates_plans' => [],
                'rates_plans_rooms' => [],
                "best_option_taken" => false,
                "best_option_cart_items_id" => [],
                'flag_migrate' => true, // Valor por defecto, para regionalizacion
            ];

            if(isset($clientHotel["hotel"]["token_search_channel"])) {
                $hotel["token_search_channel"] = $clientHotel["hotel"]["token_search_channel"];
            }

            $dataHotels[] = $hotel;
        }

        $hotels[0]["city"]["hotels"] = $dataHotels;
        $hotels[0]["city"]["min_price_search"] = number_format($min_price_search, 2, '.', '');
        $hotels[0]["city"]["max_price_search"] = number_format($max_price_search, 2, '.', '');
        $hotels[0]["city"]["quantity_hotels"] = count($hotels[0]["city"]["hotels"]);

        $faker = Faker::create();
        $token_search_frontend = $faker->unique()->uuid;

        $hotels[0]["city"]["token_search_frontend"] = $token_search_frontend;

        // Almacenar token de búsqueda para Frontend
        $this->storeTokenSearchHotels($token_search_frontend, $hotels, $this->expiration_search_hotels);

        // Retornar hoteles sanitizados
        return $hotels;
    }

    // Desde canal Aurora
    public function searchAvailabilityLegacy(Request $request, array $extraRequest = []): array
    {
        $fields = $this->validateFieldsSearchAvailability($request, $extraRequest);
        $hotelIds = array_column($fields['channelHotels'], 'id');

        // Hoteles desde A2
        $paramsHotelAvailable = [
            'client_id' => $extraRequest['client_id'] ?? null,
            'period' => $extraRequest['period'] ?? null,
            'date_from' => $extraRequest['date_from'] ?? null,
            'date_to' => $extraRequest['date_to'] ?? null, // Fecha de salida
            'from' => $extraRequest['from'] ?? null, // Fecha de entrada
            'to' => $extraRequest['to'] ?? null, // Fecha de salida
            'reservation_days' => $extraRequest['reservation_days'] ?? null,
            "country_iso" => $extraRequest['country_id'],
            "state_iso" => $extraRequest['state_id'],
            "city_id" => $extraRequest['city_id'],
            "district_id" => $extraRequest['district_id'],
            'typeclass_id' => $extraRequest['typeclass_id'],
            'hotels_id' => $hotelIds,
            'hotels_search_code' => $extraRequest['hotels_search_code'],
            'language_id' => $extraRequest['language_id'],
        ];
        $clientHotels = $this->getClientHotelsAvailable($paramsHotelAvailable);

        if (empty($clientHotels)) {
            return [];
        }

        $hotelIds = [];

        foreach ($clientHotels as $index => $clientHotel) {
            $hotelId = $clientHotel['hotel']['channels'][0]['pivot']['code'] ?? null;
            if ($hotelId) {
                $hotelIds[] = $hotelId;
            }
        }

        $hotelIds = collect($hotelIds)->unique()->toArray();

        if (!empty($hotelIds)) {
            $hotelIds = implode(", ", $hotelIds);

            $paramsSearchGateway = [
                'hotelId' => $hotelIds,
                'dateFrom' => $fields['dateFrom'],
                'dateTo' => $fields['dateTo'],
                // 'countryISO' => $fields['countryISO'], // comentado para que acepte varios países
                'quantityRooms' => $fields['quantityRooms'],
                'quantityPersonsRooms' => $fields['quantityPersonsRooms'],
            ];

            $searchResult = $this->searchAvailabilityGateway($paramsSearchGateway);

            $channelHotels = $searchResult['data'] ?? [];
            $channelTokenSearch = $searchResult['tokenSearch'] ?? null; // Capturar token de búsqueda de DynamoDB

            // Crear un mapa rápido de hoteles por ID para acceso rápido
            $channelHotelsMap = [];
            foreach ($channelHotels as $channelHotel) {
                $channelHotelId = $channelHotel['hotelId'] ?? null;
                if ($channelHotelId) {
                    $channelHotel['token_search_channel'] = $channelTokenSearch; // Nuevo campo agregado
                    $channelHotelsMap[$channelHotelId] = $channelHotel;
                }
            }
        } else {
            $channelHotelsMap = [];
        }

        foreach ($clientHotels as $index => $clientHotel) {
            $hotelId = $clientHotel['hotel']['channels'][0]['pivot']['code'] ?? null;

            if (!$hotelId || !isset($channelHotelsMap[$hotelId])) {
                unset($clientHotels[$index]);
                continue;
            }

            $channelHotel = $channelHotelsMap[$hotelId];
            $channelRooms = $channelHotel['rooms'] ?? [];
            $internalHotelId = $clientHotel['hotel']['id'];

            $remarks = $channelHotel['remarks'] ?? null;
            $remarksString = implode("\n", $remarks);
            // $channelHotel['description'] = $remarksString; // Nuevo campo agregado

            // Filtrar habitaciones de A2 con las del canal Hyperguest
            $paramsFilter = [
                'externalRooms' => $channelRooms,
                'hotel' => $clientHotel['hotel'],
                'client_id' => $extraRequest['client_id'] ?? null,
                'period' => $extraRequest['period'] ?? null,
                'countryISO' => $fields['countryISO'],
                'quantityPersonsRooms' => $fields['quantityPersonsRooms'] ?? null,
                'markup' => [
                    'markup' => $clientHotel['client_markup'],
                ],
                'set_markup' => $request->input('set_markup', 0),
                'tokenSearchChannel' => $channelHotel['token_search_channel'],
            ];

            $rooms = $this->filterChannelRooms($paramsFilter, $internalHotelId);

            $clientHotels[$index]['hotel']['token_search_channel'] = $channelHotel['token_search_channel'];
            $clientHotels[$index]['hotel']['notes'] = $remarksString;
            $clientHotels[$index]['hotel']['rooms'] = $rooms;
            $clientHotels[$index]['hotel']['price'] = $rooms[0]['best_price'] ?? null;

            if (is_null($clientHotels[$index]['hotel']['price'])) {
                unset($clientHotels[$index]);
            }
        }

        $clientHotels = array_values($clientHotels);

        $clientHotels = $this->filterHotelsWithValidPricing($clientHotels);

        return $clientHotels;
    }

    // Buscar en canal Hyperguest
    private function searchAvailabilityGateway(array $params): array
    {
        $gatewayService = new HyperguestGatewayService();

        $channelIntegration = [
            'channelIntegration' => [
                'channel' => 'hyperguest',
                'type' => 'PULL',
                'version' => 'v1',
                'isActive' => true
            ]
        ];
        $payload = array_merge($channelIntegration, $params);
        $response = $gatewayService->searchAvailability($payload);

        if (!$response['success']) {
            throw new Exception($response['error']);
        }

        return $response['result'];
    }

    // Filtrar hoteles que tengan precios válidos (mayores a 0)
    function filterHotelsWithValidPricing($clientHotels)
    {
        $resultado = [];

        foreach ($clientHotels as $hotel) {
            if (floatval($hotel['hotel']['price']) > 0) {
                $resultado[] = $hotel;
            }
        }

        return $resultado;
    }
}
