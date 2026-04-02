<?php

namespace App\Http\Services\Controllers;

use App\City;
use App\Room;
use App\Zone;
use DateTime;
use App\Hotel;
use App\Quote;
use App\State;
use Exception;
use App\Client;
use App\Markup;
use App\Ticket;
use App\BagRate;
use App\Country;
use App\Doctype;
use App\Service;
use App\Language;
use App\RoomType;
use App\Component;
use App\Inventory;
use Carbon\Carbon;
use App\RatesPlans;
use App\ChannelRoom;
use App\HotelClient;
use App\Reservation;
use App\ServiceRate;
use App\ChannelHotel;
use App\ClientSeller;
use App\InventoryBag;
use App\QuoteService;
use App\QuoteAgeChild;
use App\QuoteCategory;
use App\ServiceOrigin;
use App\BusinessRegion;
use App\ClientRatePlan;
use App\DateRangeHotel;
use App\PackageService;
use App\QuotePassenger;
use App\ComponentClient;
use App\RatesPlansRooms;
use App\ServiceComponent;
use App\ServiceOperation;
use App\ExperienceService;
use App\Http\Traits\Hotels;
use App\Http\Traits\Images;
use App\ServiceDestination;
use Faker\Factory as Faker;
use App\ComponentSubstitute;
use App\Http\Traits\Package;
use Illuminate\Http\Request;
use App\Http\Traits\Services;
use App\RatesPlansPromotions;
use App\PackageRateSaleMarkup;
use App\ServiceClientRatePlan;
use App\Http\Traits\Currencies;
use App\Mail\NotificationTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\PackageRequest;
use App\Http\Requests\ServiceRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use App\Http\Traits\ManageSearchHotel;
use App\Http\Traits\ValidateHotelSearch;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\HotelSearchRequest;
use App\Http\Services\Traits\ClientTrait;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ServiceDetailRequest;
use App\Http\Resources\Hotel\HotelResource;
use App\Http\Traits\HotelsAvailByDestination;
use App\Http\Resources\Package\PackageResource;
use App\Http\Services\Traits\ClientMarkupTrait;
use App\Http\Traits\AddHotelRateTaxesAndServices;
use App\Http\Services\Traits\ClientHotelUtilTrait;
use App\Http\Traits\CalculateHotelTxesAndServices;
use App\Http\Traits\CalculateCancellationlPolicies;
use App\Http\Services\Traits\ServiceControllerTrait;
use App\Http\Resources\Package\PackageDetailsResource;
use App\Http\Services\Traits\ClientHotelAvailableTrait;
use App\Http\Services\Traits\ClientHotelOnRequestTrait;
use App\Http\Multichannel\Hyperguest\Services\AvailabilityHyperguestGatewayService;

class ServiceController extends Controller
{
    use ManageSearchHotel,
        ValidateHotelSearch,
        CalculateCancellationlPolicies,
        CalculateHotelTxesAndServices,
        HotelsAvailByDestination,
        ClientTrait,
        Services,
        ServiceControllerTrait,
        Package,
        Images,
        Currencies,
        Hotels,
        AddHotelRateTaxesAndServices,
        ServiceControllerTrait,
        ClientHotelUtilTrait,
        ClientHotelAvailableTrait,
        ClientHotelOnRequestTrait,
        ClientMarkupTrait;

    /**
     * @var int time before expire cache search expressed in minutes
     */
    public $expiration_search_hotels = 180; // 3 horas
    public $expiration_search_services = 1440; // 24 horas

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function hotel($id, Request $request): JsonResponse
    {
        $lang = $request->input("lang");

        $hotel = Hotel::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hotel');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'currency.translations' => function ($query) use ($lang) {
                $query->where('type', 'currency');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'hoteltype.translations' => function ($query) use ($lang) {
                $query->where('type', 'hoteltype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'hotelcategory.translations' => function ($query) use ($lang) {
                $query->where('type', 'hotelcategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'typeclass.translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])
            ->with([
                'amenity' => function ($query) use ($lang) {
                    $query->where('status', 1);

                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'amenity');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        },
                    ]);
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'amenity');
                        },
                    ]);
                },
            ])->with([
                'country.translations' => function ($query) use ($lang) {
                    $query->where('type', 'country');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with([
                'state.translations' => function ($query) use ($lang) {
                    $query->where('type', 'state');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with([
                'city.translations' => function ($query) use ($lang) {
                    $query->where('type', 'city');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with([
                'district.translations' => function ($query) use ($lang) {
                    $query->where('type', 'district');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with([
                'zone.translations' => function ($query) use ($lang) {
                    $query->where('type', 'zone');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with([
                'galeries' => function ($query) {
                    $query->where('type', 'hotel');
                    // $query->where('slug', 'hotel_logo');
                }
            ])->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                }
            ])
            ->with([
                'rooms' => function ($query) use ($lang) {
                    $query->select(
                        'id',
                        'hotel_id',
                        'room_type_id',
                        'max_capacity',
                        'min_adults',
                        'max_adults',
                        'max_child',
                        'max_infants'
                    );

                    $query->where('state', 1);

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
                        'room_type' => function ($query) use ($lang) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) use ($lang) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->whereHas('language', function ($q) use ($lang) {
                                        $q->where('iso', $lang);
                                    });
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        },
                    ]);
                },
            ])->with(['alerts' => function ($query) use ($lang) {
                $query->where('year', '=', date("Y"));
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }])->with('chain')->where('id', $id)->first()->toArray();

        $hotel = new HotelResource($hotel);

        return Response::json(['success' => true, 'data' => $hotel]);
    }

    public function hotelsList(Request $request)
    {

        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        $from = Carbon::parse(date('Y-m-d'));

        $period = $from->year;

        $lang = $request->input("lang") ? $request->input("lang") : "es";
        $language = Language::where('iso', $lang)->first();

        $hotels_client = $this->getClientHotelsList($client_id, $period, null, $language->id);

        $hotels = [];

        foreach ($hotels_client as $index => $hotel_client) {
            $hotel_description = "";
            $hotel_address = "";
            $hotel_logo = "";
            $amenities = [];
            $hotel_gallery = [];
            $rooms = [];

            //cargar traducciones
            foreach ($hotel_client["translations"] as $translation) {
                if ($translation["slug"] === "hotel_address") {
                    $hotel_address = $translation["value"];
                }
                if ($translation["slug"] === "hotel_description") {
                    $hotel_description = $translation["value"];
                }
            }

            //cargar galeria de hotel
            foreach ($hotel_client["galeries"] as $image) {
                if ($image["slug"] === "hotel_logo") {
                    $hotel_logo = url('/') . '/images/' . $image["url"];
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
            if (count($hotel_client["amenity"]) > 0) {
                foreach ($hotel_client["amenity"] as $amenity) {
                    array_push(
                        $amenities,
                        [
                            "name" => $amenity["translations"][0]["value"],
                            "image" => count($amenity["galeries"]) > 0 ? url('/') . '/images/' . $amenity["galeries"][0]["url"] : '',
                        ]
                    );
                }
            }

            //cargar habitaciones de hotel
            foreach ($hotel_client["rooms"] as $room) {
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
                    array_push($room_gallery, url('/') . '/images/' . $image["url"]);
                }

                //agregar habitacion al arreglo
                array_push($rooms, [
                    'room_id' => $room["id"],
                    'room_type' => $room['room_type']['translations'][0]['value'],
                    'occupation' => $room['room_type']['occupation'],
                    'name' => $room_name,
                    'description' => $room_description,
                    'gallery' => $room_gallery,
                    'max_capacity' => $room["max_capacity"],
                    'max_adults' => $room["max_adults"],
                    'max_child' => $room["max_child"],
                    'rates' => $rates,
                ]);
            }

            $hoteltypeclass = $this->getHotelTypeClass($hotel_client["hoteltypeclass"]);
            $typeclass_name = $hoteltypeclass['name'];
            $typeclass_color = $hoteltypeclass['color'];


            array_push($hotels, [
                "id" => $hotel_client["id"],
                "name" => $hotel_client["name"],
                "country_code" => $hotel_client["country"]["iso"],
                "country" => $hotel_client["country"]["translations"][0]["value"],
                "state_code" => $hotel_client["state"]["iso"],
                "state" => $hotel_client["state"]["translations"][0]["value"],
                "city_code" => $hotel_client["city"]["id"],
                "city" => $hotel_client["city"]["translations"][0]["value"],
                "district" => isset($hotel_client["district"]["translations"]) ? $hotel_client["district"]["translations"][0]["value"] : '',
                "zone" => isset($hotel_client["zone"]["translations"]) ? $hotel_client["zone"]["translations"][0]["value"] : '',
                "description" => $hotel_description,
                "address" => $hotel_address,
                "chain" => $hotel_client["chain"]["name"],
                "logo" => $hotel_logo,
                "category" => $hotel_client["stars"],
                "type" => $hotel_client["hoteltype"]["translations"][0]["value"],
                "class" => $typeclass_name,
                // "hoteltypeclass" => $hotel_client["hoteltypeclass"],
                "color_class" => $typeclass_color, //$hotel_client["hotel"]["typeclass"]["color"],
                "coordinates" => [
                    'latitude' => $hotel_client["latitude"],
                    'longitude' => $hotel_client["longitude"],
                ],
                "popularity" => count($hotel_client["hotelpreferentials"]) > 0 ? $hotel_client["hotelpreferentials"][0]['value'] : 0,  //$hotel_client["preferential"],
                "favorite" => $this->checkHotelFavorite($hotel_client["id"]),
                "checkIn" => $hotel_client["check_in_time"],
                "checkOut" => $hotel_client["check_out_time"],
                "amenities" => $amenities,
                "galleries" => $hotel_gallery,
                "rooms" => $rooms,
                "best_option_taken" => false,
                "best_option_cart_items_id" => [],
            ]);
        }

        return Response::json([
            'success' => true,
            'data' => $hotels,
        ]);
    }

    public function hotels_readonly(Request $request)
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
            $hotels_id = ChannelHotel::on('mysql_read')->where('channel_id', 1)->whereIn(
                'code',
                $hotels_search_code
            )->pluck('hotel_id');
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
        $language = Language::on('mysql_read')->where('iso', $lang_iso)->where('state', 1)->first();
        if (!$language) {
            $language = Language::on('mysql_read')->where('iso', "en")->where('state', 1)->first();
            $lang_iso = 'en';
        }
        \App::setLocale($lang_iso);
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

        $accept_child = $quantityPersons->get('accept_child');
        $child_min_age = $quantityPersons->get('child_min_age');

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);

        $this->setClient($client_id);
        $period = $from->year;

        try {
            if (!$setMarkup) {
                $client_markup = Markup::on('mysql_read')->select('hotel')
                    ->where(['client_id' => $client_id, 'period' => $period])->first();

                if (!$client_markup) {
                    throw new Exception('El cliente seleccionado no tiene un Markup para el periodo ' . $period);
                }
            }

            if ($accept_child) {
                //Logica para verificar si los hoteles aceptan niños
                $hotels_client_hotel_id_list = $this->getClientHotelsIdsReadonly(
                    $country_id,
                    $state_id,
                    $city_id,
                    $district_id,
                    $typeclass_id,
                    $period,
                    $hotels_id,
                    $hotels_search_code,
                    $child_min_age,
                    $preferential
                );
            } else {
                $hotels_client_hotel_id_list = $this->getClientHotelsIdsReadonly(
                    $country_id,
                    $state_id,
                    $city_id,
                    $district_id,
                    $typeclass_id,
                    $period,
                    $hotels_id,
                    $hotels_search_code,
                    false,
                    $preferential
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
                $query = ClientRatePlan::on('mysql_read');
                $query->where('client_id', $client_id);
                $query->where('period', $period);
                $rate_plans_ids_ignore = $query->pluck('rate_plan_id')->toArray();

                $query = RatesPlans::on('mysql_read');
                $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
                $query->where('status', 1);
                if ($promotional_rate == 1) {
                    $query->where('promotions', 1);
                }
                $query->whereNotIn('id', $rate_plans_ids_ignore);
                $rate_plans_available = $query->pluck('id')->toArray();

                $query = Room::on('mysql_read');
                $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
                $query->where('state', 0);
                $rooms_ids_ignore = $query->pluck('id')->toArray();

                $query = RatesPlansRooms::on('mysql_read');
                $query->whereIn('rates_plans_id', $rate_plans_available);
                $query->whereNotIn('room_id', $rooms_ids_ignore);
                $query->where('status', 1);
                if (count($rate_plan_room_search)) {
                    $query->whereIn('id', $rate_plan_room_search);
                }
                $rate_plan_room_ids_include = $query->pluck('id');

                //TODO agregar a la logica que si trae niños al menos igual a 0
                //validacion de tarifas Aurora para ver si tienen una rate_plan_calendary y un importe de adulto o importe total > 0 para el rango de fechas de reservacion
                $rate_plan_room_ids_include = $this->getArrayIdsRatesPlansRoomsCalendarsInRangeReservationDaysAndAmountAdultOrAmountTotalReadonly(
                    $hotels_client_hotel_id_list,
                    $from,
                    $to,
                    $rate_plan_room_ids_include,
                    $reservation_days
                );

                //Esto es para tarifas de Channels validando tiempo de estadia y días de anticipacion de reserva
                $rate_plan_room_channels_time_stay_and_days_advance_reservation = $this->getArrayIdsRatesPlansRoomsChannelsTimeStayAndDaysAdvanceReservationReadonly(
                    $hotels_client_hotel_id_list,
                    $from,
                    $days_advance_reservation,
                    $reservation_days,
                    $rate_plan_room_search
                );

                if (count($rate_plan_room_channels_time_stay_and_days_advance_reservation) > 0) {
                    // Validacion de tarifas de channel para ver si tiene un importe para el numero de adultos de busqueda o tiene un importe total por habitacion
                    $rate_plan_room_channels_validate_calendar = $this->getArrayIdsRatesPlansRoomsChannelsCalendarReadonly(
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
                $rate_plan_room_ids_include = $this->getArrayIdsRatesPlansRoomsHaveOneInventoryReadonly(
                    $rate_plan_room_ids_include,
                    $hotels_client_hotel_id_list,
                    $from,
                    $to,
                    $reservation_days
                );

                $rates_plan_rooms_on_request = array_diff($rates_plan_rooms_on_request, $rate_plan_room_ids_include);

                $hotels_client = $this->getClientHotelsAvailReadonly(
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
                $rates_on_request = $this->getClientHotelsAvailOnRequestReadonly(
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
            foreach ($rates_on_request as $index_rate => $rate) {
                $sw = false;
                foreach ($hotels_client as $index_hotel => $hotel_client) {
                    if ($rate["room"]["hotel_id"] == $hotel_client["hotel"]["id"]) {
                        $sw = true;
                    }
                }
                if ($sw == false) {
                    $hotels_id_onrequest[$rate["room"]["hotel_id"]] = $rate["room"]["hotel_id"];
                }
            }

            if (count($hotels_id_onrequest) > 0) {
                $hotels_client_on_request = $this->getDataHotelNoReturnReadonly(
                    $hotels_id_onrequest,
                    $period,
                    $client_id,
                    $date_from,
                    $date_to,
                    $language->id
                );
                $hotels_client = array_merge($hotels_client, $hotels_client_on_request);
            }
            //validamos si hay habitaciones onRequest que no esten agregados a la matriz de los hoteles y las creamos pero si existen ya las agregamos
            foreach ($rates_on_request as $index_rate => $rate) {
                if ($rate["status"] == 1) {
                    foreach ($hotels_client as $index_hotel => $hotel_client) {
                        if ($rate["room"]["hotel_id"] == $hotel_client["hotel"]["id"]) {

                            $key = array_search($rate['room_id'], array_column($hotel_client["hotel"]["rooms"], 'id'));
                            if ($key === false) {
                                $rates_on_request[$index_rate]["status"] = 0;
                                $rate["status"] = 0;
                                $rate["room"]['rates_plan_room'][] = $rate;
                                array_push($hotels_client[$index_hotel]["hotel"]["rooms"], $rate["room"]);
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
                $amenities = [];
                $hotel_gallery = [];
                $rooms = [];

                $best_options = [];

                if (count($hotel_client["best_options"]) > 0) {
                    $best_options = [
                        "quantity_rooms" => $hotel_client["best_options"]["quantity_rooms"],
                        "quantity_adults" => $hotel_client["best_options"]["quantity_adults"],
                        "quantity_child" => $hotel_client["best_options"]["quantity_child"],
                        "total_taxes_and_services_amount" => roundLito((float)number_format(
                            $hotel_client["best_options"]["total_taxes_and_services_amount"],
                            2,
                            '.',
                            ''
                        )),
                        "total_supplements_amount" => roundLito((float)number_format(
                            $hotel_client["best_options"]["total_supplements_amount"],
                            2,
                            '.',
                            ''
                        )),
                        "total_sub_rate_amount" => roundLito((float)number_format(
                            $hotel_client["best_options"]["total_sub_rate_amount"],
                            2,
                            '.',
                            ''
                        )),
                        "total_rate_amount" => roundLito((float)number_format(
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
                        },
                    ])->get();
                    foreach ($zones as $zone) {
                        $find_zone = false;
                        if (count($hotels[0]["city"]["zones"]) == 0) {
                            $hotels[0]["city"]["zones"][] = [
                                "zone_name" => $zone["translations"][0]["value"],
                                "status" => false,
                            ];
                        } else {
                            foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                                if ($zone_new["zone_name"] == $zone["translations"][0]["value"]) {
                                    $find_zone = true;
                                }
                            }
                            if (!$find_zone) {
                                $hotels[0]["city"]["zones"][] = [
                                    "zone_name" => $zone["translations"][0]["value"],
                                    "status" => false,
                                ];
                            }
                        }
                    }
                } else {
                    $state = State::where('iso', $state_id)->first();
                    $cities = [];
                    if ($state) {
                        $cities = City::where('state_id', $state->id)->get();
                    }
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
                                    "zone_name" => $zone["translations"][0]["value"],
                                    "status" => false,
                                ];
                            } else {
                                foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                                    if ($zone_new["zone_name"] == $zone["translations"][0]["value"]) {
                                        $find_zone = true;
                                    }
                                }
                                if (!$find_zone) {
                                    $hotels[0]["city"]["zones"][] = [
                                        "zone_name" => $zone["translations"][0]["value"],
                                        "status" => false,
                                    ];
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
                            "name" => $amenity["translations"][0]["value"],
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
                        foreach ($room["rates_plan_room"] as $rate) {
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
                                        'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"],
                                        'available' => $min_inventory,
                                        'onRequest' => $rate["status"],
                                        'rateProvider' => $rate["channel"]["name"],
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
                                } else {
                                    if (!in_array($rate["id"], $ids_rates_channels_charged)) {
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
                                            'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"],
                                            'available' => $min_inventory,
                                            'onRequest' => $rate["status"],
                                            'rateProvider' => $rate["channel"]["name"],
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
                                            'show_message_error' => false,
                                            'message_error' => '',
                                        ];
                                        $ids_rates_channels_charged[] = $rate["id"];
                                    }
                                }
                            }
                        }

                        if (count($rates) == 0) {
                            continue;
                        }

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

                        //agregar habitacion al arreglo
                        $rooms[] = [
                            'room_id' => $room["id"],
                            'room_type_id' => $room["room_type_id"],
                            'room_type' => $room['room_type']['translations'][0]['value'],
                            'occupation' => $room['room_type']['occupation'],
                            'bed_additional' => $room["bed_additional"],
                            'name' => $room_name,
                            'description' => $room_description,
                            'gallery' => $room_gallery,
                            'max_capacity' => $room["max_capacity"],
                            'max_adults' => $room["max_adults"],
                            'max_child' => $room["max_child"],
                            'best_price' => $rates[0]['total'],
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

                                $rate_plan_description = '';
                                if ($rate['rate']['descriptions']) {
                                    $rate_plan_description = $rate['rate']['descriptions'][0]['value'];
                                }

                                $total_amount = number_format($rate["total_amount"], 2, '.', '');
                                $total_amount_tax = number_format($rate["total_taxes_and_services_amount"], 2, '.', '');

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
                                $rate_name = (count($rate["rate_plan"]["translations"]) > 0) ? $rate["rate_plan"]["translations"][0]["value"] : '';
                                $no_show = (count($rate["rate_plan"]["translations_no_show"]) > 0) ? $rate["rate_plan"]["translations_no_show"][0]["value"] : '';
                                $day_use = (count($rate["rate_plan"]["translations_day_use"]) > 0) ? $rate["rate_plan"]["translations_day_use"][0]["value"] : '';
                                $notes = (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes';
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
                                    'no_show' => $no_show,
                                    'day_use' => $day_use,
                                    'notes' => $notes,
                                    'total' => $total_amount,
                                    'total_taxes_and_services' => $total_amount_tax,
                                    'avgPrice' => $total_amount,
                                    'political' => [
                                        'rate' => [
                                            'name' => $rate["rate"]["calendarys"][0]["policies_rates"]["name"],
                                            //                                'message' => "@@@".$rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'],
                                            'message' => (isset($rate["rate"]["calendarys"][0]["policies_rates"]["translations"]) ? $rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
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
                $state_name = '';
                $city_name = '';
                $zone_name = '';
                $district_name = '';
                $hoteltype_name = '';
                $typeclass_name = '';

                if (!empty($hotel_client["hotel"]["country"]) and count($hotel_client["hotel"]["country"]["translations"]) > 0) {
                    $country_name = $hotel_client["hotel"]["country"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["state"]) and count($hotel_client["hotel"]["state"]["translations"]) > 0) {
                    $state_name = $hotel_client["hotel"]["state"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["city"]) and count($hotel_client["hotel"]["city"]["translations"]) > 0) {
                    $city_name = $hotel_client["hotel"]["city"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["zone"]) and count($hotel_client["hotel"]["zone"]["translations"]) > 0) {
                    $zone_name = $hotel_client["hotel"]["zone"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["district"]) and count($hotel_client["hotel"]["district"]["translations"]) > 0) {
                    $district_name = $hotel_client["hotel"]["district"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["hoteltype"]) and count($hotel_client["hotel"]["hoteltype"]["translations"]) > 0) {
                    $hoteltype_name = $hotel_client["hotel"]["hoteltype"]["translations"][0]["value"];
                }

                // if (!empty($hotel_client["hotel"]["typeclass"]) and count($hotel_client["hotel"]["typeclass"]["translations"]) > 0) {
                //     $typeclass_name = $hotel_client["hotel"]["typeclass"]["translations"][0]["value"];
                // }


                $hoteltypeclass = $this->getHotelTypeClass($hotel_client["hotel"]["hoteltypeclass"]);
                $typeclass_name = $hoteltypeclass['name'];
                $typeclass_color = $hoteltypeclass['color'];


                $hotels[0]["city"]["hotels"][] = [
                    "id" => $hotel_client["hotel"]["id"],
                    "code" => $code_ ? $code_->code : $hotel_client["hotel"]["id"],
                    "flag_new" => $hotel_client["hotel"]["flag_new"],
                    "date_end_flag_new" => $hotel_client["hotel"]["date_end_flag_new"],
                    "name" => $hotel_client["hotel"]["name"],
                    "country" => $country_name,
                    "state" => $state_name,
                    "city" => $city_name,
                    "district" => $district_name,
                    "zone" => $zone_name,
                    "description" => $hotel_description,
                    "address" => $hotel_address,
                    "summary" => $hotel_summary,
                    "notes" => (isset($hotel_client["hotel"]["notes"])) ? $hotel_client["hotel"]["notes"] : '',
                    "chain" => $hotel_client["hotel"]["chain"]["name"],
                    "logo" => $hotel_logo,
                    "category" => (int)($hotel_client["hotel"]["stars"]),
                    "type" => $hoteltype_name,
                    "class" => $typeclass_name,
                    "hoteltypeclass" => $hotel_client["hotel"]["hoteltypeclass"],
                    "color_class" => $typeclass_color, //$hotel_client["hotel"]["typeclass"]["color"],
                    "price" => $princeHotel,
                    "coordinates" => [
                        'latitude' => $hotel_client["hotel"]["latitude"],
                        'longitude' => $hotel_client["hotel"]["longitude"],
                    ],
                    "popularity" => count($hotel_client["hotel"]["hotelpreferentials"]) > 0 ? $hotel_client["hotel"]["hotelpreferentials"][0]['value'] : 0,  //$hotel_client["hotel"]["preferential"],
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
                ];
            }

            $hotels[0]["city"]["min_price_search"] = number_format($min_price_search, 2, '.', '');
            $hotels[0]["city"]["max_price_search"] = number_format($max_price_search, 2, '.', '');
            $hotels[0]["city"]["quantity_hotels"] = count($hotels[0]["city"]["hotels"]);

            $token_search_frontend = $faker->unique()->uuid;
            $hotels[0]["city"]["token_search_frontend"] = $token_search_frontend;

            $this->storeTokenSearchHotels($token_search_frontend, $hotels, $this->expiration_search_hotels);

            $response_z = [
                'success' => true,
                'data' => $hotels,
                'expiration_token' => $this->expiration_search_hotels,
            ];

            Cache::put('response_', $response_z, 3600);

            return Response::json($response_z);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_line' => $e->getLine()
            ]);
        }
    }

    public function hotels(Request $request): \Illuminate\Http\JsonResponse
    {
        //Todo Obtenemos el Id del cliente
        $client_id = $this->getClientId($request);

        $priceRange = $request->get('price_range');

        $hotels_id = $request->get('hotels_id');
        if (!isset($hotels_id)) {
            $hotels_id = [];
        }

        $userTypeId = Auth::user()->user_type_id;

        //Todo Obtenemos los parametros de busqueda
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
        app()->setLocale($lang_iso);
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

        try {
            if (!$setMarkup) {

                $client_markup = Markup::whereHas('businessRegion.countries', function ($query) use ($country_id) {
                    $query->where('iso', $country_id);
                })->where(['client_id' => $client_id, 'period' => $period])->first();

                // $client_markup = Markup::select('hotel')
                //     ->where(['client_id' => $client_id, 'period' => $period])
                //     ->first();

                if (!$client_markup) {
                    throw new Exception(trans('validations.quotes.client_does_not_have_markup_for_year', ['year' => $period]));
                }
            }

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
                    $child_min_age,
                    $preferential
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
                    $hotels_search_code,
                    false,
                    $preferential
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
                if ($userTypeId !== 3) {
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

                // SI ES HYPERGUEST PULL , REVISAR DISPONIBILIDAD
                $isHyperguestPull = false;

                if ($isHyperguestPull){

                }
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

            // dd($hotels_client);

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
                    $from,
                    $typeclass_id,
                    $country_id
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
                        "total_taxes_and_services_amount" => roundLito((float)number_format(
                            $hotel_client["best_options"]["total_taxes_and_services_amount"],
                            2,
                            '.',
                            ''
                        )),
                        "total_supplements_amount" => roundLito((float)number_format(
                            $hotel_client["best_options"]["total_supplements_amount"],
                            2,
                            '.',
                            ''
                        )),
                        "total_sub_rate_amount" => roundLito((float)number_format(
                            $hotel_client["best_options"]["total_sub_rate_amount"],
                            2,
                            '.',
                            ''
                        )),
                        "total_rate_amount" => roundLito((float)number_format(
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
                        },
                    ])->get();
                    foreach ($zones as $zone) {
                        $find_zone = false;
                        if (count($hotels[0]["city"]["zones"]) == 0) {
                            $hotels[0]["city"]["zones"][] = [
                                "zone_name" => $zone["translations"][0]["value"],
                                "status" => false,
                            ];
                        } else {
                            foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                                if ($zone_new["zone_name"] == $zone["translations"][0]["value"]) {
                                    $find_zone = true;
                                }
                            }
                            if (!$find_zone) {
                                $hotels[0]["city"]["zones"][] = [
                                    "zone_name" => $zone["translations"][0]["value"],
                                    "status" => false,
                                ];
                            }
                        }
                    }
                } else {
                    $state = State::where('iso', $state_id)->first();
                    $cities = [];
                    if ($state) {
                        $cities = City::where('state_id', $state->id)->get();
                    }
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
                                    "zone_name" => $zone["translations"][0]["value"],
                                    "status" => false,
                                ];
                            } else {
                                foreach ($hotels[0]["city"]["zones"] as $zone_new) {
                                    if ($zone_new["zone_name"] == $zone["translations"][0]["value"]) {
                                        $find_zone = true;
                                    }
                                }
                                if (!$find_zone) {
                                    $hotels[0]["city"]["zones"][] = [
                                        "zone_name" => $zone["translations"][0]["value"],
                                        "status" => false,
                                    ];
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

                if (isset($hotel_client["hotel"]["alerts"]) and count($hotel_client["hotel"]["alerts"]) > 0) {
                    $hotel_summary = $hotel_client["hotel"]["alerts"][0]["notes"];
                    $hotel_notes = $hotel_client["hotel"]["alerts"][0]["remarks"];
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
                            "name" => $amenity["translations"][0]["value"],
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

                // dd("PEOPLE", $quantity_persons_rooms);

                foreach ($quantity_persons_rooms as $key_room => $quantity_persons_room) {
                    foreach ($hotel_client["hotel"]["rooms"] as $room) {
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
                        foreach ($room["rates_plan_room"] as $rate) {
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

                            $inventory = null;

                            if (is_null($inventory) && count($rate['inventories']) >= $reservation_days)
                            {
                                $min_inventory = $rate["inventories"][0]["inventory_num"] ?? 0;

                                foreach($rate['inventories'] as $inventory)
                                {
                                    if($inventory['inventory_num'] < $min_inventory)
                                    {
                                        $min_inventory = $inventory['inventory_num'];
                                    }
                                }

                                $inventory = $min_inventory;
                            }

                            /*$session_name = sprintf('rate_%s', $rate['id']);

                            if(empty($request->session()->get($session_name)))
                            {
                                $request->session()->put($session_name, $inventory);
                            }
                            else
                            {
                                $inventory = $request->session()->get($session_name);
                            }*/

                            if($inventory > 0)
                            {
                                $rate["status"] = ($inventory > $key_room) ? 1 : 0;
                            }
                            else
                            {
                                $rate['status'] = 0;
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

                            if (!$zeroRates && $rates_plan_room_new["total_amount"] <= 0) {
                                continue;
                            }

                            if ($rates_plan_room_new && $check_other_step) {
                                $guest_quantity = 1;
                                $rooms_quantity = 1;

                                //calculo de detalle de politicas de cancelacion
                                if ($rate["channel_id"] == 1) {
                                    if (count($rates_plan_room_new["calendarys"]) == 0) {
                                        continue;
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
                                    $max_occupancy = $rates_plan_room_new["calendarys"][0]["policies_rates"]["max_occupancy"] ?? 0;
                                } else {
                                    $max_occupancy = $rates_plan_room_new["calendarys"][0]["max_occupancy"] ?? $room["max_capacity"];
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
                                                RatesPlansPromotions::where(
                                                    'rates_plans_id',
                                                    $rate["rate_plan"]["id"]
                                                )->get();
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
                                        'price_dynamic' => isset($rate["rate_plan"]["price_dynamic"]) ? $rate["rate_plan"]["price_dynamic"] : 0,
                                        'promotions_data' => $promotions_data_,
                                        'name_commercial' => $rate["rate_plan"]["name"],
                                        'name' => $rate_name,
                                        'description' => $rate_plan_description,
                                        'meal_id' => $rate["rate_plan"]["meal"]['id'],
                                        'meal_name' => $rate["rate_plan"]["meal"]["translations"][0]["value"],
                                        'available' => $min_inventory,
                                        'onRequest' => $rate["status"],
                                        'rateProvider' => $rate["channel"]["name"],
                                        'no_show' => $no_show,
                                        'day_use' => $day_use,
                                        'notes' => $notes,
                                        'total' => $total_amount,
                                        'total_taxes_and_services' => $total_amount_tax,
                                        'avgPrice' => $total_amount,
                                        'political' => [
                                            'rate' => [
                                                'name' => $rates_plan_room_new["calendarys"][0]["policies_rates"]["name"] ?? 'Default',
                                                'message' => (isset($rates_plan_room_new["calendarys"][0]["policies_rates"]["translations"]) ? $rates_plan_room_new["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
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
                                                    RatesPlansPromotions::where(
                                                        'rates_plans_id',
                                                        $rate["rate_plan"]["id"]
                                                    )->get();
                                            }
                                        }
                                        $rate_name = (count($rate["rate_plan"]["translations"]) > 0) ? $rate["rate_plan"]["translations"][0]["value"] : '';
                                        $no_show = (count($rate["rate_plan"]["translations_no_show"]) > 0) ? $rate["rate_plan"]["translations_no_show"][0]["value"] : '';
                                        $day_use = (count($rate["rate_plan"]["translations_day_use"]) > 0) ? $rate["rate_plan"]["translations_day_use"][0]["value"] : '';
                                        $notes = (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes';
                                        $rates[] = [
                                            'rateId' => $rate["id"],
                                            'ratePlanId' => $rate["rate_plan"]["id"],
                                            'price_dynamic' => isset($rate["rate_plan"]["price_dynamic"]) ? $rate["rate_plan"]["price_dynamic"] : 0,
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
                                            'no_show' => $no_show,
                                            'day_use' => $day_use,
                                            'notes' => $notes,
                                            'total' => $total_amount,
                                            'total_taxes_and_services' => $total_amount_tax,
                                            'avgPrice' => $total_amount,
                                            'political' => [
                                                'rate' => [
                                                    'name' => $rates_plan_room_new["calendarys"][0]["policies_rates"]["name"] ?? 'Default',
                                                    'message' => (isset($rates_plan_room_new["calendarys"][0]["policies_rates"]["translations"]) ? $rates_plan_room_new["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
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
                                            'show_message_error' => false,
                                            'message_error' => '',
                                        ];
                                        $ids_rates_channels_charged[] = $rate["id"];
                                    }
                                }
                            }
                        }

                        if (count($rates) == 0) {
                            continue;
                        }

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

                        //agregar habitacion al arreglo
                        $rooms[] = [
                            'room_id' => $room["id"],
                            'room_type_id' => $room["room_type_id"],
                            'room_type' => $room['room_type']['translations'][0]['value'],
                            'occupation' => $room['room_type']['occupation'],
                            'bed_additional' => $room["bed_additional"],
                            'name' => $room_name,
                            'description' => $room_description,
                            'gallery' => $room_gallery,
                            'max_capacity' => $room["max_capacity"],
                            'max_adults' => $room["max_adults"],
                            'max_child' => $room["max_child"],
                            'best_price' => $rates[0]['total'],
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

                                $rate_plan_description = '';
                                if ($rate['rate']['descriptions']) {
                                    $rate_plan_description = $rate['rate']['descriptions'][0]['value'];
                                }

                                $total_amount = number_format($rate["total_amount"], 2, '.', '');
                                $total_amount_tax = number_format($rate["total_taxes_and_services_amount"], 2, '.', '');

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

                                $rate_name = (count($rate["rate_plan"]["translations"]) > 0) ? $rate["rate_plan"]["translations"][0]["value"] : '';
                                $no_show = (count($rate["rate_plan"]["translations_no_show"]) > 0) ? $rate["rate_plan"]["translations_no_show"][0]["value"] : '';
                                $day_use = (count($rate["rate_plan"]["translations_day_use"]) > 0) ? $rate["rate_plan"]["translations_day_use"][0]["value"] : '';
                                $notes = (count($rate["rate_plan"]["translations_notes"]) > 0) ? $rate["rate_plan"]["translations_notes"][0]["value"] : 'No notes';
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
                                    'no_show' => $no_show,
                                    'day_use' => $day_use,
                                    'notes' => $notes,
                                    'total' => $total_amount,
                                    'total_taxes_and_services' => $total_amount_tax,
                                    'avgPrice' => $total_amount,
                                    'political' => [
                                        'rate' => [
                                            'name' => $rate["rate"]["calendarys"][0]["policies_rates"]["name"],
                                            //                                'message' => "@@@".$rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'],
                                            'message' => (isset($rate["rate"]["calendarys"][0]["policies_rates"]["translations"]) ? $rate["rate"]["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
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
                $state_name = '';
                $city_name = '';
                $zone_name = '';
                $district_name = '';
                $hoteltype_name = '';

                if (!empty($hotel_client["hotel"]["country"]) and count($hotel_client["hotel"]["country"]["translations"]) > 0) {
                    $country_name = $hotel_client["hotel"]["country"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["state"]) and count($hotel_client["hotel"]["state"]["translations"]) > 0) {
                    $state_name = $hotel_client["hotel"]["state"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["city"]) and count($hotel_client["hotel"]["city"]["translations"]) > 0) {
                    $city_name = $hotel_client["hotel"]["city"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["zone"]) and count($hotel_client["hotel"]["zone"]["translations"]) > 0) {
                    $zone_name = $hotel_client["hotel"]["zone"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["district"]) and count($hotel_client["hotel"]["district"]["translations"]) > 0) {
                    $district_name = $hotel_client["hotel"]["district"]["translations"][0]["value"];
                }

                if (!empty($hotel_client["hotel"]["hoteltype"]) and count($hotel_client["hotel"]["hoteltype"]["translations"]) > 0) {
                    $hoteltype_name = $hotel_client["hotel"]["hoteltype"]["translations"][0]["value"];
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

                $filteredRooms = $this->onlyRatesHyperguestAndAuroraQuotes($rooms);

                $hotels[0]["city"]["hotels"][] = [
                    "id" => $hotel_client["hotel"]["id"],
                    "code" => $code_ ? $code_->code : $hotel_client["hotel"]["id"],
                    "flag_new" => $hotel_client["hotel"]["flag_new"],
                    "date_end_flag_new" => $hotel_client["hotel"]["date_end_flag_new"],
                    "name" => $hotel_client["hotel"]["name"],
                    "country" => $country_name,
                    "state" => $state_name,
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
                    "popularity" => count($hotel_client["hotel"]["hotelpreferentials"]) > 0 ? $hotel_client["hotel"]["hotelpreferentials"][0]['value'] : 0,  //$hotel_client["hotel"]["preferential"],
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
                    "rooms" => $filteredRooms,
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

            $this->storeTokenSearchHotels($token_search_frontend, $hotels, $this->expiration_search_hotels);

            $response_z = [
                'success' => true,
                'data' => $hotels,
                'expiration_token' => $this->expiration_search_hotels,
            ];

            Cache::put('response_', $response_z, 3600);

            return Response::json($response_z);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_line' => $e->getLine()
            ]);
        }
    }

    public function hotels_services(HotelSearchRequest $request)
    {
        $destiny = $request->input('destiny');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $type_classes = $request->input('type_classes');
        $client_id = $request->input('client_id', $this->getClientPackageDefaultId());
        $query_search = $request->input('query_search');
        $allWords = $request->input('allWords');
        $hotel_id = $request->input('hotel_id', null);

        $country_id = "";
        $state_id = "";
        $city_id = "";
        $district_id = "";
        $hyperguest_pull = false;
        $success = false;

        //separar codigos de destino
        if (!$query_search) {
            $locationCodes = $this->extractLocationCodes($destiny);
            $country_id = $locationCodes['country_id'];
            $state_id = $locationCodes['state_id'];
            $city_id = $locationCodes['city_id'];
            $district_id = $locationCodes['district_id'];
        }

        if (!$country_id) {
            return response()->json([
                'success' => false,
                'data'  => [],
                'message' => 'You have not selected a country',
            ]);
        }

        // TODO: Obtenemos el la región del cliente en base al pais
        $client_business = $this->getClientBusinessRegion($client_id, $country_id);

        if(!$client_business)
        {
            return response()->json([
                'success' => false,
                'data'  => [],
                'message' => 'You have not selected a region',
            ]);
        }

        if (!is_array($type_classes)) {
            $type_classes = [$type_classes];
        }

        $from = Carbon::parse($date_from);
        $period_markup = $from->year;

        $client_markup = $this->getClientMarkup($client_id, $period_markup);

        if (!$client_markup)
        {
            return response()->json([
                'success' => false,
                'data'  => [],
                'message' => 'Profit margin is needed for hyperguest hotels - (CLDEPA) CLIENT PACKAGE'
            ]);
        }

        $response1 = $this->auroraHotelsResponse($date_from, $date_to, $query_search ?? '', $allWords ?? '', $country_id, $state_id, $city_id, $district_id, $type_classes, $client_id, $hotel_id);

        $response2 = $this->hyperguestHotelsResponse($date_from, $date_to, $type_classes, $client_id, $request);

        $hotels = $this->mergeAuroraAndHyperguestHotels($response1['data'][0]['city']['hotels'], $response2['data'][0]['city']['hotels'], $this->expiration_search_hotels);

        $error = '';
        if ($response1['success'] == false) {
            $error .= ' Aurora: ' . ($response1['error'] ?? 'Error searching hotels in Aurora service.');
        }

        if ($response2['success'] == false) {
            $error .= ' Hyperguest: ' . ($response2['error'] ?? 'Error searching hotels in Hyperguest service.');
        }

        return response()->json([
            'success' => ($response1['success'] && $response2['success']),
            'data'  => $hotels,
            'message' => $error,
        ]);

    }

    public function auroraHotelsResponse(
        string $date_from,
        string $date_to,
        ?string $query_search = '',
        ?string $allWords = null,
        string $country_id,
        string $state_id,
        string $city_id,
        string $district_id,
        array $type_classes,
        int $client_id,
        int $hotel_id = null
    ): array {

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);
        $period = $from->year;

        $from = $from->format('Y-m-d');
        $to = $to->subDay(1)->format('Y-m-d');

        // 1. Construir query de hoteles
        $hotelQuery = $this->buildHotelSearchQuery(
            $query_search,
            $allWords,
            $country_id,
            $state_id,
            $city_id,
            $district_id,
            $type_classes,
            $from,
            $hotel_id
        );

        // 2. Cargar relaciones
        $hotelQuery = $this->loadRelationHotel($hotelQuery, $from, $to, $client_id, $period);

        // 3. Ejecutar query
        $hotels = $hotelQuery->get();

         // 4. Aplicar markup y tarifas
         if ($client_id) {
             $client_markup = $this->getClientMarkup($client_id, $period);

             $this->setClient($client_id);

             $hotels = $this->applyClientMarkupToHotels($hotels, $client_markup);
         } else {
             $hotels = $this->processHotelsWithoutClient($hotels);
         }

         // 5. Convertir a array y asignar typeclass
         $hotels = $hotels->toArray();

         $hotels = $this->assignHotelTypeClass($hotels, $date_from, $type_classes);

         $hotels = array_map(function ($item) {
            $item['hyperguest_pull'] = false;
            return $item;
         }, $hotels);
         // IGUALAMOS LA ESTRUCTURA DE LA RESPUESTA, PARA QUE SEA COMPATIBLE CON HYPERGUEST
         $response = [
             'success' => true,
             'data' => [
                 [
                     'city' => [
                         'hotels' => $hotels,
                     ]
                 ]
             ],
             'expiration_token' => $this->expiration_search_hotels,
        ];

        return $response;
    }

    public function hyperguestHotelsResponse(string $date_from, string $date_to, array $type_classes, int $client_id, $request): array {
        $range_date = $this->convertDateRange($date_from, $date_to);

        $newRequestData = $request->all();
        $newRequestData['date_from'] = $range_date['new_start_date'];
        $newRequestData['date_to'] = $range_date['new_end_date'];
        $newRequestData['typeclass_id'] = $type_classes[0] ?? '';

        $newRequest = new HotelSearchRequest($newRequestData);

        $availabilityHGService = new AvailabilityHyperguestGatewayService();
        $response = $availabilityHGService->hotels($newRequest, $client_id);


        $hotels = $response['data'][0]['city']['hotels'] ?? [];

        $hotels = array_map(function ($item) {
            $item['hyperguest_pull'] = true;
            return $item;
         }, $hotels);

        $response['data'][0]['city']['hotels'] = $hotels;

        $response = $this->processHyperguestHotelData($response, $date_from, $date_to, $type_classes);

        return $response;
    }

    public function hotel_services(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $quote_id = $request->input('quote_id');
        $admin = $request->input('admin');

        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);

        $from = $from->format('Y-m-d');
        $to = $to->subDay(1)->format('Y-m-d');

        $hotel = Hotel::with([
            'country' => function ($query) {
                $query->select('id','iso');

                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'country');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'state' => function ($query) {
                $query->select('id');

                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'state');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'city' => function ($query) {
                $query->select('id');

                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'city');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'district' => function ($query) {
                $query->select('id');

                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'district');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'zone' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'zone');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'translations' => function ($query) {
                $query->select('object_id', 'value', 'slug');
                $query->where('type', 'hotel');
                $query->where('language_id', 1);
            },
        ])->with([
            'galeries' => function ($query) {
                $query->select('object_id', 'slug', 'url');
                $query->where('type', 'hotel');
            },
        ])->with([
            'amenity' => function ($query) {
                $query->where('status', 1);

                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'amenity');
                        $query->where('language_id', 1);
                    },
                ]);
                $query->with([
                    'galeries' => function ($query) {
                        $query->select('object_id', 'url');
                        $query->where('type', 'amenity');
                    },
                ]);
            },
        ])->with([
            'hoteltype' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'hoteltype');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'typeclass' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'typeclass');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'rooms' => function ($query) use ($from, $to, $admin) {
                $query->select(
                    'id',
                    'hotel_id',
                    'room_type_id',
                    'max_capacity',
                    'min_adults',
                    'max_adults',
                    'max_child',
                    'max_infants'
                );

                $query->where('state', 1);

                $query->with([
                    'galeries' => function ($query) {
                        $query->select('object_id', 'url');
                        $query->where('type', 'room');
                    },
                ]);

                $query->with([
                    'room_type' => function ($query) {
                        $query->select('id');
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
                $query->with([
                    'rates_plan_room' => function ($query) use ($from, $to, $admin) {
                        $query->select('id', 'rates_plans_id', 'room_id', 'status', 'bag', 'channel_id');
                        $query->with('channel');
                        $query->with([
                            'calendarys' => function ($query) use ($from, $to) {
                                $query->where('date', '>=', $from);
                                $query->where('date', '<=', $to);
                                $query->with([
                                    'policies_rates' => function ($query) {
                                        $query->with([
                                            'policies_cancelation' => function ($query) {
                                                $query->with('policy_cancellation_parameter');
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with('rate');
                            },
                        ]);
                        $query->with([
                            'rate_plan' => function ($q) use ($admin) {
                                $q->with('meal.translations');
                                if ($admin) {
                                    $q->withTrashed();
                                }
                            },
                        ]);
                        $query->with([
                            'inventories' => function ($query) use ($from, $to) {
                                $query->where('date', '>=', $from);
                                $query->where('date', '<=', $to);
                            },
                        ]);
                        $query->with([
                            'markup' => function ($query) use ($from, $to) {
                                $query->where('period', Carbon::parse($from)->year);
                            },
                        ]);
                    },
                ]);
                // $query->with('service_');
            },
        ])->where('id', $hotel_id);

        if ($hotel->count() == 0) {
            return Response::json(['success' => false, 'data' => "Hotel no encontrado"]);
        } else {
            $hotel = $hotel->first();
        }

        if ($admin) {
            $markup = 0;
        } else {
            $markup = Quote::where('id', $quote_id)->first()->markup;
        }

        foreach ($hotel["rooms"] as $room) {
            if (count($room["rates_plan_room"]) > 0) {
                foreach ($room["rates_plan_room"] as $rate_plan_room) {
                    foreach ($rate_plan_room["calendarys"] as $calendar) {
                        $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_adult"] + ($calendar["rate"][0]["price_adult"] * ($markup / 100));
                    }
                }
            }
        }
        return Response::json(['success' => true, 'data' => $hotel]);
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

        $client = Client::with(['businessRegions.countries'])->find($client_id);

        $countryIds = $client->businessRegions
            ->flatMap(function ($region) {
                return $region->countries->pluck('iso');
            })
            ->values()
            ->all();

        $regionIds = $client->businessRegions->pluck('id');

        if ($request->get('hotels_id')) {
            //$client_id = 1;
            $hotels_client = $this->getClientHotels(
                $client_id,
                Carbon::now('America/Lima')->year,
                $request->get('hotels_id'),
                $regionIds
            );
        } else {
            //$client_id = 1;
            $hotels_client = $this->getClientHotels($client_id, Carbon::now('America/Lima')->year, null, $regionIds);
        }

        $destinations_country_state = $this->checkCountryState($hotels_client);

        $destinations_country_state_city = $this->checkCountryStateCity($hotels_client);

        $destinations_country_state_city_district = $this->checkCountryStateCityDistrict($hotels_client);

        $destinations = array_merge($destinations_country_state, $destinations_country_state_city);

        $destinations = array_merge($destinations, $destinations_country_state_city_district);

        $destination_select = [];


        if (!$countryIds) {
            return response()->json(['error' => 'Región no encontrada'], 404);
        }

        foreach ($destinations as $destination) {
            $ids = explode(",", $destination['ids']);
            if (in_array($ids[0], $countryIds)) {
                array_push($destination_select, [
                    "code" => $destination["ids"],
                    "label" => $destination["description"],
                ]);
            }
        }
        sort($destination_select);

        return Response::json($destination_select);
    }

    public function destinationsQuote(Request $request)
    {
        $lang = $request->input('lang');

        $hotel = Hotel::with([
            'country' => function ($query) use ($lang) {
                $query->select('id', 'iso');

                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'country');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    },
                ]);
            },
        ])->with([
            'state' => function ($query) use ($lang) {
                $query->select('id', 'iso');

                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'state');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    },
                ]);
            },
        ])
            ->with([
                'city' => function ($query) use ($lang) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) use ($lang) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        },
                    ]);
                },
            ])->get();

        $destinations_country_state = $this->checkCountryStatePackage($hotel);

        $destinations_country_state_city = $this->checkCountryStateCityPackage($hotel);

        $destinations_country_state_city_district = $this->checkCountryStateCityDistrictPackage($hotel);

        $destinations = array_merge($destinations_country_state, $destinations_country_state_city);

        $destinations = array_merge($destinations, $destinations_country_state_city_district);

        $destination_select = [];

        foreach ($destinations as $destination) {
            array_push($destination_select, [
                "code" => $destination["ids"],
                "label" => $destination["description"],
            ]);
        }

        return Response::json($destination_select);
    }

    public function destinationsHotels(Request $request)
    {
        $lang = $request->input('lang');

        $hotel = Hotel::with([
            'country' => function ($query) use ($lang) {
                $query->select('id', 'iso');

                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'country');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    },
                ]);
            },
        ])->with([
            'state' => function ($query) use ($lang) {
                $query->select('id', 'iso');

                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'state');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    },
                ]);
            },
        ])
            ->with([
                'city' => function ($query) use ($lang) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) use ($lang) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        },
                    ]);
                },
            ])->get();

        // $destinations_country_state = $this->checkCountryStateById($hotel);

        // $destinations_country_state_city = $this->checkCountryStateCityById($hotel);

        // $destinations_country_state_city_district = $this->checkCountryStateCityDistrictById($hotel);

        // $destinations = $destinations_country_state;
        // $destinations = array_merge($destinations_country_state, $destinations_country_state_city);

        // $destinations = array_merge($destinations, $destinations_country_state_city_district);

        // $destination_select = [];

        // foreach ($destinations as $destination) {
        //     array_push($destination_select, [
        //         "code" => $destination["ids"],
        //         "label" => $destination["description"],
        //     ]);
        // }


        $hotels = $hotel;
        $grouped = [];

        foreach ($hotels as $hotel) {

            $countryId   = (string) $hotel["country"]["id"];
            $countryName = $hotel["country"]["translations"][0]["value"];

            $stateId   = (string) $hotel["state"]["id"];
            $stateName = $hotel["state"]["translations"][0]["value"];

            // Crear país si no existe
            if (!isset($grouped[$countryId])) {
                $grouped[$countryId] = [
                    "country" => [
                        "code"  => $countryId,
                        "label" => $countryName
                    ],
                    "states" => []
                ];
            }

            // Evitar estados duplicados
            $stateKey = $countryId . ',' . $stateId;

            if (!isset($grouped[$countryId]["states"][$stateKey])) {
                $grouped[$countryId]["states"][$stateKey] = [
                    "code"  => $stateKey,
                    "label" => $stateName . "," . $countryName
                ];
            }
        }

        /**
         * ORDENAR PAÍSES ALFABÉTICAMENTE
         */
        usort($grouped, function ($a, $b) {
            return strcmp($a["country"]["label"], $b["country"]["label"]);
        });

        /**
         * CONSTRUIR JSON PLANO FINAL
         */
        $result = [];

        foreach ($grouped as $item) {

            // Agregar país
            $result[] = $item["country"];

            // Ordenar estados
            $states = array_values($item["states"]);

            usort($states, function ($a, $b) {
                return strcmp($a["label"], $b["label"]);
            });

            // Agregar estados
            foreach ($states as $state) {
                $result[] = $state;
            }
        }

        return $result;


        return Response::json($destination_select);
    }


    private function checkCountryState($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["hotel"]["country"]["iso"] . ',' . $hotel_client["hotel"]["state"]["iso"],
                    "description" => $hotel_client["hotel"]["country"]["translations"][0]["value"] . "," . $hotel_client["hotel"]["state"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel_client["hotel"]["country"]["iso"] . ',' . $hotel_client["hotel"]["state"]["iso"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["hotel"]["country"]["iso"] . ',' . $hotel_client["hotel"]["state"]["iso"],
                        "description" => $hotel_client["hotel"]["country"]["translations"][0]["value"] . "," . $hotel_client["hotel"]["state"]["translations"][0]["value"],
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStatePackage($hotels)
    {
        $destinations = [];

        foreach ($hotels as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["country"]["iso"] . ',' . $hotel_client["state"]["iso"],
                    "description" => $hotel_client["country"]["translations"][0]["value"] . "," . $hotel_client["state"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel_client["country"]["iso"] . ',' . $hotel_client["state"]["iso"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["country"]["iso"] . ',' . $hotel_client["state"]["iso"],
                        "description" => $hotel_client["country"]["translations"][0]["value"] . "," . $hotel_client["state"]["translations"][0]["value"],
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStateCityPackage($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["country"]["iso"] . ',' . $hotel_client["state"]["iso"] . ',' . $hotel_client["city_id"],
                    "description" => $hotel_client["country"]["translations"][0]["value"] . ', ' . $hotel_client["state"]["translations"][0]["value"] . ', ' . $hotel_client["city"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == $hotel_client["country"]["iso"] . ',' . $hotel_client["state"]["iso"] . ',' . $hotel_client["city_id"]) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["country"]["iso"] . ',' . $hotel_client["state"]["iso"] . ',' . $hotel_client["city_id"],
                        "description" => $hotel_client["country"]["translations"][0]["value"] . ', ' . $hotel_client["state"]["translations"][0]["value"] . ', ' . $hotel_client["city"]["translations"][0]["value"],
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStateCityDistrictPackage($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {

            $id_string_country_id = $hotel_client["country"]["iso"];

            $id_string_state_id = "," . $hotel_client["state"]["iso"];

            $id_string_city_id = $hotel_client["city_id"] ? "," . $hotel_client["city_id"] : '';

            $id_string_district_id = $hotel_client["district_id"] ? "," . $hotel_client["district_id"] : '';

            $ids_string = $id_string_country_id . $id_string_state_id . $id_string_city_id . $id_string_district_id;

            $country_name = $hotel_client["country"]["translations"][0]["value"];
            $state_name = $hotel_client["state"]["translations"][0]["value"] ? ',' . $hotel_client["state"]["translations"][0]["value"] : '';
            $city_name = $hotel_client["city"]["translations"][0]["value"] ? ',' . $hotel_client["city"]["translations"][0]["value"] : '';
            $district_name = (!empty($hotel_client["district"]["translations"]) and $hotel_client["district"]["translations"][0]["value"]) ? ',' . $hotel_client["district"]["translations"][0]["value"] : '';
            if ($id_string_district_id) {
                if (count($destinations) === 0) {
                    array_push($destinations, [
                        "ids" => $ids_string,
                        "description" => $country_name . $state_name . $city_name . $district_name,
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
                            "description" => $country_name . $state_name . $city_name . $district_name,
                        ]);
                    }
                }
            }
        }
        return $destinations;
    }


    private function checkCountryStateById($hotels)
    {
        $destinations = [];

        foreach ($hotels as $hotel) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel["country"]["id"] . ',' . $hotel["state"]["id"],
                    "description" => $hotel["state"]["translations"][0]["value"] . "," .$hotel["country"]["translations"][0]["value"] ,
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel["country"]["id"] . ',' . $hotel["state"]["id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel["country"]["id"] . ',' . $hotel["state"]["id"],
                        "description" => $hotel["state"]["translations"][0]["value"] . "," . $hotel["country"]["translations"][0]["value"]  ,
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStateCityById($hotels)
    {
        $destinations = [];

        foreach ($hotels as $hotel) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel["country"]["id"] . ',' . $hotel["state"]["id"] . ',' . $hotel["city_id"],
                    "description" => $hotel["city"]["translations"][0]["value"]  . ', ' . $hotel["state"]["translations"][0]["value"] . ', ' . $hotel["country"]["translations"][0]["value"]   ,
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == $hotel["country"]["id"] . ',' . $hotel["state"]["id"] . ',' . $hotel["city_id"]) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel["country"]["id"] . ',' . $hotel["state"]["id"] . ',' . $hotel["city_id"],
                        "description" => $hotel["city"]["translations"][0]["value"] . ', ' .$hotel["state"]["translations"][0]["value"]  . ', ' . $hotel["country"]["translations"][0]["value"]   ,
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStateCityDistrictById($hotels)
    {
        $destinations = [];

        foreach ($hotels as $hotel) {

            $id_string_country_id = $hotel["country"]["id"];

            $id_string_state_id = "," . $hotel["state"]["id"];

            $id_string_city_id = $hotel["city_id"] ? "," . $hotel["city_id"] : '';

            $id_string_district_id = $hotel["district_id"] ? "," . $hotel["district_id"] : '';

            $ids_string = $id_string_country_id . $id_string_state_id . $id_string_city_id . $id_string_district_id;

            $country_name = $hotel["country"]["translations"][0]["value"];
            $state_name = $hotel["state"]["translations"][0]["value"] ? ',' . $hotel["state"]["translations"][0]["value"] : '';
            $city_name = $hotel["city"]["translations"][0]["value"] ? ',' . $hotel["city"]["translations"][0]["value"] : '';
            $district_name = (!empty($hotel["district"]["translations"]) and $hotel["district"]["translations"][0]["value"]) ? ',' . $hotel["district"]["translations"][0]["value"] : '';
            if ($id_string_district_id) {
                if (count($destinations) === 0) {
                    array_push($destinations, [
                        "ids" => $ids_string,
                        "description" => $country_name . $state_name . $city_name . $district_name,
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
                            "description" => $country_name . $state_name . $city_name . $district_name,
                        ]);
                    }
                }
            }
        }
        return $destinations;
    }

    public function calculateRateTotalAmount(Request $request)
    {
        $token_search = $request->input('token_search');
        $hotel_id = $request->input('hotel_id');
        $room_id = $request->input('room_id');
        $rate_id = $request->input('rate_id');
        $rate_plan_id = $request->input('rate_plan_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $quantity_adults = $request->input('quantity_adults');
        $quantity_child = $request->input('quantity_child');
        $zeroRates = $request->get('zero_rates', false);

        $ages_child = $request->input('ages_child');

        $room_type_id = Room::where('id', $room_id)->first()->room_type_id;

        $room_occupation = RoomType::where('id', $room_type_id)->first()->occupation;

        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        $this->setClient($client_id);

        $hotels = $this->getHotelsByTokenSearch($token_search);

        if (!array_key_exists("error", $hotels)) {

            foreach ($hotels as $hotel) {
                if ($hotel["hotel"]["id"] == $hotel_id) {
                    // Filtar lo Texes and services que seran aplicadon al hotel segun si el cliente es local o extranjero
                    $applicable_fees = $this->getHotelApplicableFees($this->client(), $hotel['hotel']);

                    foreach ($hotel["hotel"]["rooms"] as $room) {
                        if ($room["id"] == $room_id) {
                            foreach ($room["rates_plan_room"] as $rate_plan_room) {
                                if ($rate_plan_room["id"] == $rate_id) {
                                    $markupFromSearch = $this->getMarkupFromsearch(
                                        $hotel['client_markup'],
                                        $hotel['hotel']['markup'],
                                        $rate_plan_room
                                    );

                                    // Por revisar..
                                    $quantity_persons_search = $quantity_adults + $quantity_child;

                                    if ($rate_plan_room["channel_id"] == 1) {
                                        $rate_occupancy = $room['room_type']['occupation'];
                                    } else {
                                        $rate_occupancy = $rate_plan_room["calendarys"][0]["max_occupancy"];
                                    }
                                    //check exist price extra
                                    $check_exist_price_extra = false;
                                    $quantity_check_price_extra = 0;
                                    for ($j = 0; $j < count($rate_plan_room["calendarys"]); $j++) {
                                        if ($rate_plan_room["calendarys"][$j]["rate"][0]["price_extra"] > 0 || $rate_plan_room["calendarys"][$j]["rate"][0]["price_total"] > 0) {
                                            $quantity_check_price_extra++;
                                        }
                                    }
                                    if ($quantity_check_price_extra == count($rate_plan_room["calendarys"])) {
                                        $check_exist_price_extra = true;
                                    }

                                    if ($quantity_adults >= 1 && $quantity_adults <= $room["max_adults"] && $quantity_child >= 0 && $quantity_child <= $room["max_child"]) {

                                        if ($quantity_persons_search <= $room["max_capacity"]) {
                                            $quantity_persons = $this->getQuantityPersons(
                                                $quantity_adults,
                                                $quantity_child,
                                                $room["max_capacity"],
                                                $rate_occupancy,
                                                $room["min_adults"],
                                                $room["max_adults"],
                                                $room["max_child"]
                                            );
                                            if ($quantity_persons["quantity_extras"] > 0) {
                                                if ($check_exist_price_extra) {
                                                    if ($rate_plan_room["channel_id"] == 1) {
                                                        $rate_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                                                            $markupFromSearch,
                                                            $quantity_persons["quantity_adults"],
                                                            $quantity_persons["quantity_child"],
                                                            $quantity_persons["quantity_extras"],
                                                            $ages_child,
                                                            $hotel['hotel'],
                                                            $room["bed_additional"],
                                                            $room
                                                        );
                                                    } else {
                                                        if (strtoupper($rate_plan_room['channel']['code']) === 'HYPERGUEST') {
                                                            $rate_plan_room_new = $this->getChannelsAvailableRates(
                                                                $markupFromSearch,
                                                                $quantity_persons["quantity_adults"],
                                                                $quantity_persons["quantity_child"],
                                                                $ages_child,
                                                                $hotel['hotel'],
                                                                $room
                                                            );
                                                        } else {
                                                            $rate_plan_room_new = $this->calculateRatePlanRoomChannelsCalendarByPersons(
                                                                $this->getMarkupFromsearch(
                                                                    $hotel['client_markup'],
                                                                    $hotel['hotel']['markup'],
                                                                    $rate_plan_room
                                                                ),
                                                                $rate_plan_room["id"],
                                                                $room["max_capacity"],
                                                                $quantity_persons["quantity_adults"],
                                                                $quantity_persons["quantity_child"],
                                                                $quantity_persons["quantity_extras"]
                                                            );

                                                            if (!$rate_plan_room_new) {
                                                                continue;
                                                            }
                                                        }
                                                    }

                                                    if (!$zeroRates && $rate_plan_room_new["total_amount"] <= 0) {
                                                        continue;
                                                    }

                                                    $supplements = $this->calculateRateSupplementsRequired(
                                                        $rate_plan_id,
                                                        $hotel_id,
                                                        $date_from,
                                                        $date_to,
                                                        $client_id,
                                                        $quantity_adults,
                                                        $quantity_child,
                                                        [],
                                                        $rate_plan_room_new['markup']
                                                    );

                                                    $taxes_and_services = $this->addHotelExtraFees(
                                                        $applicable_fees,
                                                        $rate_plan_room["rate_plan"],
                                                        $rate_plan_room_new["total_amount"]
                                                    );

                                                    $rate_plan_room_new["total_amount"] += $taxes_and_services["amount_fees"] + $supplements["total_amount"];
                                                    $rate_plan_room_new["total_amount"] = number_format(
                                                        $rate_plan_room_new["total_amount"],
                                                        2,
                                                        '.',
                                                        ''
                                                    );

                                                    $rate_plan_room_new["taxes_and_services"] = $taxes_and_services;
                                                    $rate_plan_room_new["total_taxes_and_services"] = $taxes_and_services["amount_fees"];
                                                    $rate_plan_room_new["supplements"] = $supplements;

                                                    return Response::json([
                                                        'success' => true,
                                                        'rate_plan_room' => $rate_plan_room_new,
                                                    ]);
                                                } else {
                                                    return Response::json([
                                                        'success' => false,
                                                        'error' => "Esta tarifa acepta hasta " . $rate_occupancy . " personas en esta habitación",
                                                    ]);
                                                }
                                            } else {
                                                if ($rate_plan_room["channel_id"] == 1) {
                                                    $rate_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                                                        $this->getMarkupFromsearch(
                                                            $hotel['client_markup'],
                                                            $hotel['hotel']['markup'],
                                                            $rate_plan_room
                                                        ),
                                                        $quantity_persons["quantity_adults"],
                                                        $quantity_persons["quantity_child"],
                                                        0,
                                                        $ages_child,
                                                        $hotel['hotel'],
                                                        $room["bed_additional"],
                                                        $room
                                                    );
                                                } else {
                                                    if (strtoupper($rate_plan_room['channel']['code']) === 'HYPERGUEST') {
                                                        $rate_plan_room_new = $this->getChannelsAvailableRates(
                                                            $markupFromSearch,
                                                            $quantity_persons["quantity_adults"],
                                                            $quantity_persons["quantity_child"],
                                                            $ages_child,
                                                            $hotel['hotel'],
                                                            $room
                                                        );
                                                    } else {
                                                        $rate_plan_room_new = $this->calculateRatePlanRoomChannelsCalendarByPersons(
                                                            $this->getMarkupFromsearch(
                                                                $hotel['client_markup'],
                                                                $hotel['hotel']['markup'],
                                                                $rate_plan_room
                                                            ),
                                                            $rate_plan_room["id"],
                                                            $room["max_capacity"],
                                                            $quantity_persons["quantity_adults"],
                                                            $quantity_persons["quantity_child"],
                                                            0
                                                        );

                                                        if (!$rate_plan_room_new) {
                                                            continue;
                                                        }
                                                    }
                                                }

                                                if (!$zeroRates && $rate_plan_room_new["total_amount"] <= 0) {
                                                    continue;
                                                }

                                                $supplements = $this->calculateRateSupplementsRequired(
                                                    $rate_plan_id,
                                                    $hotel_id,
                                                    $date_from,
                                                    $date_to,
                                                    $client_id,
                                                    $quantity_adults,
                                                    $quantity_child,
                                                    [],
                                                    $rate_plan_room_new['markup']
                                                );

                                                $taxes_and_services = $this->addHotelExtraFees(
                                                    $applicable_fees,
                                                    $rate_plan_room["rate_plan"],
                                                    $rate_plan_room_new["total_amount"]
                                                );

                                                $rate_plan_room_new["total_amount"] += $taxes_and_services["amount_fees"] + $supplements["total_amount"];
                                                $rate_plan_room_new["total_amount"] = number_format(
                                                    $rate_plan_room_new["total_amount"],
                                                    2,
                                                    '.',
                                                    ''
                                                );
                                                $rate_plan_room_new["taxes_and_services"] = $taxes_and_services;
                                                $rate_plan_room_new["total_taxes_and_services"] = $taxes_and_services["amount_fees"];
                                                $rate_plan_room_new["supplements"] = $supplements;
                                                return Response::json([
                                                    'success' => true,
                                                    'rate_plan_room' => $rate_plan_room_new,
                                                ]);
                                            }
                                        } else {
                                            return Response::json([
                                                'success' => false,
                                                'error' => "Esta habitación acepta hasta " . $room["max_capacity"] . " personas",
                                            ]);
                                        }
                                    } else {
                                        return Response::json([
                                            'success' => false,
                                            'error' => "Datos Ingresados Invalidos",
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return Response::json(['success' => false, 'error' => $hotels["error"]]);
        }
    }

    public function calculate_selection_rate_total_amount(Request $request)
    {
        $token_search = $request->input('token_search');
        $hotel_id = $request->input('hotel_id');
        $room_id = $request->input('room_id');
        $rate_id = $request->input('rate_id');
        $rate_plan_id = $request->input('rate_plan_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $selectet_rooms = $request->input('rooms');
        $ages_child = $request->input('ages_child');
        $zeroRates = $request->get('zero_rates', false);

        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        try {
            $client_id = $this->getClientId($request);

            // $isHyperguestPull = ChannelHotel::where('hotel_id',$hotel_id)->where('channel_id', 6)->where('type', 2)->where('state', 1)->exists();
            $isHyperguestPull = RatesPlans::where('id', $rate_plan_id)->where('channel_id', 6)->where('type_channel', 2)->exists();

            if (!$isHyperguestPull) {
                $this->setClient($client_id);

                $current_date = Carbon::now('America/Lima')->startOfDay();
                $check_in = Carbon::parse($date_from);
                $check_out = Carbon::parse($date_to);

                $date_to_supplement = Carbon::parse($date_to);
                $date_to_supplement = $date_to_supplement->subDay(1)->format('Y-m-d');

                $hotels = $this->getHotelsByTokenSearch($token_search);

                $rate_plan_rooms = [];
                if (!array_key_exists("error", $hotels)) {
                    foreach ($hotels as $hotel) {
                        if ($hotel["hotel"]["id"] == $hotel_id) {
                            // Filtar lo Texes and services que seran aplicadon al hotel segun si el cliente es local o extranjero
                            $applicable_fees = $this->getHotelApplicableFees($this->client(), $hotel['hotel']);

                            foreach ($hotel["hotel"]["rooms"] as $room) {
                                if ($room["id"] == $room_id) {
                                    foreach ($room["rates_plan_room"] as $rate_plan_room) {
                                        if ($rate_plan_room["id"] == $rate_id) {
                                            $markupFromSearch = $this->getMarkupFromsearch(
                                                $hotel['client_markup'],
                                                $hotel['hotel']['markup'],
                                                $rate_plan_room
                                            );

                                            foreach ($selectet_rooms as $selectet_room) {
                                                $quantity_adults = $selectet_room['quantity_adults'];
                                                $quantity_child = $selectet_room['quantity_child'];

                                                $quantity_persons_search = $quantity_adults + $quantity_child;

                                                if ($quantity_persons_search == 0) {
                                                    throw new Exception("La busqueda debe tener al menos un pasajero");
                                                }

                                                if ($rate_plan_room["channel_id"] == 1) {
                                                    $rate_occupancy = $room["max_capacity"];
                                                } else {
                                                    $rate_occupancy = $rate_plan_room["calendarys"][0]["max_occupancy"];
                                                }

                                                //check exist price extra
                                                $check_exist_price_extra = false;
                                                $quantity_check_price_extra = 0;
                                                for ($j = 0; $j < count($rate_plan_room["calendarys"]); $j++) {
                                                    if ($rate_plan_room["calendarys"][$j]["rate"][0]["price_extra"] > 0 || $rate_plan_room["calendarys"][$j]["rate"][0]["price_total"] > 0) {
                                                        $quantity_check_price_extra++;
                                                    }
                                                }

                                                if ($quantity_check_price_extra == count($rate_plan_room["calendarys"])) {
                                                    $check_exist_price_extra = true;
                                                }

                                                $quantity_persons = $this->getQuantityPersons(
                                                    $quantity_adults,
                                                    $quantity_child,
                                                    $room["max_capacity"],
                                                    $rate_occupancy,
                                                    $room["min_adults"],
                                                    $room["max_adults"],
                                                    $room["max_child"]
                                                );

                                                if ($quantity_persons["quantity_extras"] > 0) {
                                                    if ($check_exist_price_extra) {
                                                        if ($rate_plan_room["channel_id"] == 1) {
                                                            $rate_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                                                                $markupFromSearch,
                                                                $quantity_persons["quantity_adults"],
                                                                $quantity_persons["quantity_child"],
                                                                $quantity_persons["quantity_extras"],
                                                                $selectet_room['ages_child'],
                                                                $hotel['hotel'],
                                                                $room["bed_additional"],
                                                                $room
                                                            );
                                                        } else {
                                                            if (strtoupper($rate_plan_room['channel']['code']) === 'HYPERGUEST') {
                                                                $rate_plan_room_new = $this->getChannelsAvailableRates(
                                                                    $markupFromSearch,
                                                                    $quantity_persons["quantity_adults"],
                                                                    $quantity_persons["quantity_child"],
                                                                    $selectet_room['ages_child'],
                                                                    $hotel['hotel'],
                                                                    $room
                                                                );
                                                            } else {
                                                                $rate_plan_room_new = $this->calculateRatePlanRoomChannelsCalendarByPersons(
                                                                    $this->getMarkupFromsearch(
                                                                        $hotel['client_markup'],
                                                                        $hotel['hotel']['markup'],
                                                                        $rate_plan_room
                                                                    ),
                                                                    $rate_plan_room["id"],
                                                                    $room["max_capacity"],
                                                                    $quantity_persons["quantity_adults"],
                                                                    $quantity_persons["quantity_child"],
                                                                    $quantity_persons["quantity_extras"]
                                                                );

                                                                if (!$rate_plan_room_new) {
                                                                    continue;
                                                                }
                                                            }
                                                        }

                                                        if (!$zeroRates && $rate_plan_room_new["total_amount"] <= 0) {
                                                            throw new Exception($rate_plan_room_new['show_message_error'] ? $rate_plan_room_new['message_error'] : 'Error rate');
                                                        }

                                                        $supplements = $this->calculateRateSupplementsRequired(
                                                            $rate_plan_id,
                                                            $hotel_id,
                                                            $date_from,
                                                            $date_to_supplement,
                                                            $client_id,
                                                            $quantity_persons["quantity_adults"] + $quantity_persons["quantity_extras"],
                                                            $quantity_persons["quantity_child"],
                                                            [],
                                                            $rate_plan_room_new['markup']
                                                        );

                                                        $rate_plan_room_new["total_amount"] += $supplements["total_amount"];
                                                        $taxes_and_services = $this->addHotelExtraFees(
                                                            $applicable_fees,
                                                            $rate_plan_room["rate_plan"],
                                                            $rate_plan_room_new["total_amount"]
                                                        );

                                                        $rate_plan_room_new["total_amount"] += $taxes_and_services["amount_fees"];
                                                        $rate_plan_room_new["total_amount"] = number_format(
                                                            $rate_plan_room_new["total_amount"],
                                                            2,
                                                            '.',
                                                            ''
                                                        );

                                                        $rate_plan_room_new["taxes_and_services"] = $taxes_and_services;
                                                        $rate_plan_room_new["total_taxes_and_services"] = $taxes_and_services["amount_fees"];
                                                        $rate_plan_room_new["supplements"] = $supplements;

                                                        $rate_plan_rooms[] = $rate_plan_room_new;

                                                        continue;
                                                    } else {
                                                        throw new Exception("Esta tarifa acepta hasta " . $rate_occupancy . " personas en esta habitación");
                                                    }
                                                } else {
                                                    if ($rate_plan_room["channel_id"] == 1) {
                                                        $rate_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                                                            $markupFromSearch,
                                                            $quantity_persons["quantity_adults"],
                                                            $quantity_persons["quantity_child"],
                                                            0,
                                                            $selectet_room['ages_child'],
                                                            $hotel['hotel'],
                                                            $room["bed_additional"],
                                                            $room
                                                        );
                                                    } else {
                                                        if (strtoupper($rate_plan_room['channel']['code']) === 'HYPERGUEST') {
                                                            $rate_plan_room_new = $this->getChannelsAvailableRates(
                                                                $markupFromSearch,
                                                                $quantity_persons["quantity_adults"],
                                                                $quantity_persons["quantity_child"],
                                                                $selectet_room['ages_child'],
                                                                $hotel['hotel'],
                                                                $room
                                                            );
                                                        } else {
                                                            $rate_plan_room_new = $this->calculateRatePlanRoomChannelsCalendarByPersons(
                                                                $this->getMarkupFromsearch(
                                                                    $hotel['client_markup'],
                                                                    $hotel['hotel']['markup'],
                                                                    $rate_plan_room
                                                                ),
                                                                $rate_plan_room["id"],
                                                                $room["max_capacity"],
                                                                $quantity_persons["quantity_adults"],
                                                                $quantity_persons["quantity_child"],
                                                                0
                                                            );

                                                            if (!$rate_plan_room_new) {
                                                                continue;
                                                            }
                                                        }
                                                    }

                                                    if (!$zeroRates && $rate_plan_room_new["total_amount"] <= 0) {
                                                        throw new Exception($rate_plan_room_new['show_message_error'] ? $rate_plan_room_new['message_error'] : 'Error rate');
                                                    }

                                                    $supplements = $this->calculateRateSupplementsRequired(
                                                        $rate_plan_id,
                                                        $hotel_id,
                                                        $date_from,
                                                        $date_to_supplement,
                                                        $client_id,
                                                        $quantity_persons["quantity_adults"] + $quantity_persons["quantity_extras"],
                                                        $quantity_persons["quantity_child"],
                                                        [],
                                                        $rate_plan_room_new['markup']
                                                    );

                                                    $rate_plan_room_new["total_amount"] += $supplements["total_amount"];

                                                    $taxes_and_services = $this->addHotelExtraFees(
                                                        $applicable_fees,
                                                        $rate_plan_room["rate_plan"],
                                                        $rate_plan_room_new["total_amount"]
                                                    );

                                                    $rate_plan_room_new["total_amount"] += $taxes_and_services["amount_fees"];
                                                    $rate_plan_room_new["total_amount"] = number_format(
                                                        $rate_plan_room_new["total_amount"],
                                                        2,
                                                        '.',
                                                        ''
                                                    );

                                                    $rate_plan_room_new["taxes_and_services"] = $taxes_and_services;
                                                    $rate_plan_room_new["total_taxes_and_services"] = $taxes_and_services["amount_fees"];
                                                    $rate_plan_room_new["supplements"] = $supplements;

                                                    $rate_plan_rooms[] = $rate_plan_room_new;
                                                }
                                            }

                                            // sacamos la cantidad de habitaciones seleccionadas
                                            $rooms_quantity = count($selectet_rooms);
                                            // contamos la cantidad total de pasajeros seleccionados
                                            $guest_quantity = 0;
                                            foreach ($selectet_rooms as $selectet_room) {
                                                $guest_quantity += $selectet_room['quantity_adults'];
                                                $guest_quantity += $selectet_room['quantity_child'];
                                            }

                                            if ($guest_quantity == 0) {
                                                continue;
                                            }

                                            $policies = [];
                                            foreach ($rate_plan_rooms as $roomInd => $room) {
                                                if ($room['channel_id'] == 1) {
                                                    $selected_policies_cancelation = collect($room["calendarys"][0]["policies_rates"]["policies_cancelation"]);
                                                } else {
                                                    if (count($room['policies_cancelation']) == 0) {
                                                        $selected_policies_cancelation = collect($room["calendarys"][0]["policies_cancelation"]);
                                                    } else {
                                                        $selected_policies_cancelation = collect($room['policies_cancelation']);
                                                    }
                                                }

                                                $policy_cancellation_calculated = $this->calculateCancellationlPolicies(
                                                    $current_date,
                                                    $check_in,
                                                    $check_out,
                                                    $room["total_amount"],
                                                    $selected_policies_cancelation,
                                                    $guest_quantity,
                                                    $rooms_quantity
                                                );

                                                $rate_plan_rooms[$roomInd]['policy_cancellation'] = $policy_cancellation_calculated['next_penalty'];
                                                $rate_plan_rooms[$roomInd]['policies_cancellation'] = $policy_cancellation_calculated['penalties'];

                                                $policies[] = $policy_cancellation_calculated;
                                            }

                                            $message = $policies[0]['next_penalty']["message"] ?? '';
                                            $cancellation_details = [];
                                            if (isset($policies[0]['selected_policy_cancelation']["policy_cancellation_parameter"])) {
                                                foreach ($policies[0]['selected_policy_cancelation']["policy_cancellation_parameter"] as $detail) {
                                                    array_push($cancellation_details, [
                                                        'to' => $detail["min_day"],
                                                        'from' => $detail["max_day"],
                                                        'amount' => $detail["amount"],
                                                        'tax' => $detail["tax"],
                                                        'service' => $detail["service"],
                                                    ]);
                                                }
                                            } else {
                                                array_push($cancellation_details, [
                                                    'to' => 0,
                                                    'from' => 0,
                                                    'amount' => 100,
                                                    'tax' => 1,
                                                    'service' => 1,
                                                    'penalty' => 'total_reservation',
                                                ]);
                                            }

                                            return Response::json([
                                                'success' => true,
                                                'rate_plan_rooms' => $rate_plan_rooms,
                                                'political' => [
                                                    'rate' => [
                                                        'name' => $rate_plan_rooms[0]["calendarys"][0]["policies_rates"]["name"] ?? '',
                                                        'message' => (isset($rate_plan_rooms[0]["calendarys"][0]["policies_rates"]["translations"]) ? $rate_plan_rooms[0]["calendarys"][0]["policies_rates"]["translations"][0]['value'] : ''),
                                                    ],
                                                    'cancellation' => [
                                                        "name" => $message,
                                                        "details" => $cancellation_details,
                                                    ],
                                                ],
                                            ]);

                                            break 3;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    return Response::json(['success' => false, 'error' => $hotels["error"]]);
                }
            } else {
                /* TODO: ALEX QUISPE */

                $availabilityHGService = new AvailabilityHyperguestGatewayService();
                $result = $availabilityHGService->calculate_selection_rate_total_amount($request, $client_id);
                return Response::json($result);

                /* TODO: ALEX QUISPE */
            }


        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function checkTokenSearchExternal($token_search)
    {
        if ($this->checkTokenSearch($token_search)) {
            return Response::json(['success' => true]);
        } else {
            return Response::json([
                'success' => false,
                'error' => "Su busqueda ha Expirado por favor realice de nuevo la busqueda",
            ]);
        }
    }

    public function destinations_services(Request $request)
    {

        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        try {
            $regionIds = [];

            if ($request->has('region_id')) {
                $region_id = $request->input('region_id');
                $region = BusinessRegion::with('countries')->find($region_id);
                $countryIds = $region->countries->pluck('id')->toArray();
                $regionIds[] = $region_id;
            } else {
                $client = Client::with(['businessRegions.countries'])->find($client_id);
                $countryIds = $client->businessRegions
                    ->flatMap(function ($region) {
                        return $region->countries->pluck('id');
                    })
                    ->values()
                    ->all();

                $regionIds = $client->businessRegions->pluck('id');
            }

            $withCountry = ($request->has('with_country') and $request->get('with_country') == true) ? true : false;

            $withPeriod = ($request->has('with_period') == true) ? $request->input('with_period') : Carbon::now('America/Lima')->year;

            if ($request->get('services_id')) {
                $service_client = $this->getClientServices(
                    $client_id,
                    $withPeriod,
                    true,
                    $request->get('services_id'),
                    $regionIds
                );
            } else {
                $service_client = $this->getClientServices(
                    $client_id,
                    Carbon::now('America/Lima')->year,
                    true,
                    null,
                    $regionIds
                );
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

            if (!$countryIds) {
                return response()->json(['error' => 'Región no encontrada'], 404);
            }


            foreach ($destinations as $destination) {
                $ids = explode(",", $destination['ids']);
                if (in_array($ids[0], $countryIds)) {
                    array_push($destination_select, [
                        "code" => $destination["ids"],
                        "label" => $destination["description"],
                    ]);
                }
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
                $ids = explode(",", $origin['ids']);
                if (in_array($ids[0], $countryIds)) {
                    array_push($origin_select, [
                        "code" => $origin["ids"],
                        "label" => $origin["description"],
                    ]);
                }
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

    /**
     * Muestra todos los servicios asociados a un cliente
     * @param Request $request ->client_id   Id del cliente
     * @param Request $request ->origin      Ids de las ciudad de origen
     * @param Request $request ->destiny     Ids de las ciudad de destino
     * @return \Illuminate\Http\JsonResponse
     */
    public function services(ServiceRequest $request)
    {

        $client_id = $this->getClientId($request);
        $countriesAll = $this->getCountryRegionClient($client_id);
        $countries = array_column($countriesAll, 'country_id');

        // return response()->json($countries);
        //id ó ids de servicios
        $service_id = $request->get('services_id');
        if (!isset($service_id)) {
            $service_id = null;
        }

        $limit = $request->get('limit');
        if (isset($limit)) {
            if ($request->get('limit') <= 50) {
                $limit = $request->get('limit');
            }
        } else {
            $limit = 50;
        }

        $recommendations = $request->get('recommendations');

        $compensation = ($request->has('compensation')) ? $request->get('compensation') : false;

        //Todo Variable cuando quieren asignar un markup de forma obligatoria al servicio (Ejm: Cotizador)
        $setMarkup = $request->get('set_markup');
        if (!isset($setMarkup)) {
            $setMarkup = 0;
        }

        //idioma
        $lang = ($request->has('lang')) ? $request->get('lang') : 'en';
        $language = Language::where('iso', $lang)->first();
        if ($language->count() > 0 and isset($lang)) {
            $language_id = $language->id;
        } else {
            $lang = 'es';
            $language_id = 1;
        }

        //origen - destino
        $origin = $request->get('origin');

        if (!isset($origin)) {
            $origin = null;
        }

        $destiny = $request->get('destiny');
        if (!isset($destiny)) {
            $destiny = null;
        }

        //fecha
        $date_to = $request->get('date');
        if (!isset($date_to)) {
            $date_to = Carbon::now()->format('Y-m-d H:m');
        }

        $filtro_miselaneo = false;
        $category = $request->get('category');
        if (!isset($category)) {
            $category = null;
        } else {
            $service_mask = $request->get('service_mask', null);
            if ($service_mask === true) {
                if (is_array($category) and count($category) == 1) {
                    if ($category[0] == "24") {
                        $filtro_miselaneo = true;
                    }
                }
            }
        }

        $classification = $request->get('classification');
        if (!isset($classification)) {
            $classification = null;
        }

        $experience = $request->get('experience');
        if (!isset($experience)) {
            $experience = null;
        }

        $filter = $request->get('filter');
        if (!isset($filter)) {
            $filter = null;
        }

        $quantity_persons = $request->get('quantity_persons');
        if (!isset($quantity_persons)) {
            $quantity_persons = [
                'adults' => 2,
                'child' => 0,
                'age_childs' => [
                    'age' => 0,
                ],
            ];
        }

        $supplements_has = $request->get('supplements');
        if (!isset($supplements_has)) {
            $supplements_has = true;
        } else {
            $supplements_has = $request->get('supplements');
        }

        $type = $request->get('type'); // compoartido / privado / ninguno
        if (!isset($type)) {
            $type = null;
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
            'quantity_persons' => $quantity_persons,
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
                    $child_min_age = (int)$child["age"];
                }
                if ($child["age"] < $child_min_age) {
                    $child_min_age = (int)$child["age"];
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
        $current_date = Carbon::createFromFormat('Y-m-d H:i:s', $current_date, 'America/Lima');

        $time = Carbon::now('America/Lima')->format('H:i:00');
        $date_to_time = Carbon::parse($date_to)->format('Y-m-d') . ' ' . $time;
        $check_in = Carbon::createFromFormat('Y-m-d H:i:s', $date_to_time, 'America/Lima');
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
            $dayOfWeek,
            $compensation,
            $filtro_miselaneo,
            $countries
        );

        $services_collect = $services_collect->get();

        // return Response::json(['success' => true, 'data' => $services_collect]);

        $services_collect = $services_collect->map(function ($services) use ($quantity_persons) {
            $services->day_inventory_locked = false;
            $services->validate_children_ages = true;
            $age_children = $quantity_persons['age_childs'];
            $quantity_children = $quantity_persons['child'];

            if (
                isset($services->service_rate[0]) &&
                isset($services->service_rate[0]['inventory'][0]) &&
                $services->service_rate[0]['inventory'][0]['locked'] == 1
            ) {
                $services->day_inventory_locked = true;
            }

            if ($quantity_children > 0) {
                if ($services->allow_child or $services->allow_infant) {
                    if ($services->children_ages->count() > 0) {

                        $services->validate_children_ages = true;
                        // la validacion de las edades de los niños ya no la hacemos poruqe ahora se pasa automaticamente adultos si no cumple las edades solo vamos alertar a los usuarios
                        $children_ages = $services->children_ages->first();
                        foreach ($age_children as $age) {
                            if (($children_ages->min_age <= $age['age'] and $children_ages->max_age >= $age['age']) or
                                ($services->infant_min_age <= $age['age'] and $services->infant_max_age >= $age['age'])
                            ) {
                                $services->alerta_change_children_ages = false;
                                // break;
                            } else {
                                $services->alerta_change_children_ages = true;
                                break;
                            }
                        }
                    } else {
                        $services->validate_children_ages = false;
                        $services->alerta_change_children_ages = false;
                    }
                } else {
                    $services->validate_children_ages = false;
                    $services->alerta_change_children_ages = false;
                }
            }

            if ($services->service_mask != 1) {
                if (count($services->service_rate) == 0) {
                } else {
                    return $services;
                }
            } else {
                return $services;
            }
        })->filter(function ($value) {
            if (isset($value['day_inventory_locked'])) {
                return $value['day_inventory_locked'] === false and $value['validate_children_ages'] === true;
            }
        });

        $services_collect = $services_collect->paginate($limit);

        $services_client = $services_collect->toArray();

        // $origin_country_id
        // $country = Country::find($origin_country_id)->businessRegions->get();

        // return response()->json($origin_country_id);

        $services = [];
        $min_price_search = 0;
        $max_price_search = 0;
        $faker = Faker::create();
        $token_search = $faker->unique()->uuid;
        $services_client['data'] = array_values($services_client['data']);
        foreach ($services_client['data'] as $index_service => $service_client) {
            try {

                $services[$index_service] = $this->reformat_data(
                    $service_client,
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
                    $service_client['infant_min_age'],
                    $service_client['infant_max_age'],
                    $countriesAll
                );

                // FOR COMPONENTS
                $head_component = ServiceComponent::where('service_id', $service_client['id']);

                $components = [];
                if ($head_component->count() > 0) {
                    $head_component = $head_component->first();
                    $components_ = [];

                    $data_components = Component::where('service_component_id', $head_component->id)
                        ->orderBy('after_days')->get();

                    $i_c_ = 0;
                    foreach ($data_components as $i_c => $data_component) {

                        $to_ = Carbon::parse($date_to)->addDays($data_component->after_days);
                        $dayOfWeek_ = strtolower($to_->englishDayOfWeek);
                        $date_to_ = $to_->format('Y-m-d');

                        $component = $this->getServicesClient(
                            [$data_component->service_id],
                            $current_date,
                            $to_,
                            $origin_country_id,
                            $destiny_country_id,
                            $origin_state_id,
                            $origin_city_id,
                            $origin_zone_id,
                            $destiny_state_id,
                            $destiny_city_id,
                            $destiny_zone_id,
                            null,
                            $category,
                            $classification,
                            $experience,
                            $type,
                            $period,
                            $lang, // $lang
                            $language_id,
                            $quantity_total_pax,
                            $child_min_age,
                            $check_in,
                            $recommendations,
                            $dayOfWeek_,
                            false,
                            false,
                            $countries
                        );
                        $component = $component->get()->toArray();

                        if (count($component) === 0) {
                            continue;
                        }

                        $token_search_ = $faker->unique()->uuid;
                        $components_[$i_c_] = $this->reformat_data(
                            $component[0],
                            $client_id,
                            $date_to_,
                            $dayOfWeek_,
                            $check_in,
                            $current_date,
                            $to_,
                            $quantity_child,
                            $quantity_adults,
                            $child_min_age,
                            $language_id,
                            $quantity_persons,
                            $supplements_has,
                            $quantity_total_pax,
                            $setMarkup,
                            $token_search_
                        );

                        $components_[$i_c_]['component_id'] = $data_component->id;
                        $components_[$i_c_]['lock'] = $data_component->lock;
                        $components_[$i_c_]['service_id'] = $components_[$i_c_]['id'];
                        $components_[$i_c_]['after_days'] = $data_component->after_days;
                        $components_[$i_c_]['sub_type'] = 'component';

                        $substitutes = ComponentSubstitute::where(
                            'component_id',
                            $components_[$i_c_]['component_id']
                        )->get();
                        $substitutes_ = [];
                        if (count($substitutes) > 0) {
                            $s = 0;
                            foreach ($substitutes as $substitute) {
                                $sub_collect = $this->getServicesClient(
                                    [$substitute['service_id']],
                                    $current_date,
                                    $to_,
                                    $origin_country_id,
                                    $destiny_country_id,
                                    $origin_state_id,
                                    $origin_city_id,
                                    $origin_zone_id,
                                    $destiny_state_id,
                                    $destiny_city_id,
                                    $destiny_zone_id,
                                    null,
                                    $category,
                                    $classification,
                                    $experience,
                                    $type,
                                    $period,
                                    $lang, // $lang
                                    $language_id,
                                    $quantity_total_pax,
                                    $child_min_age,
                                    $check_in,
                                    $recommendations,
                                    $dayOfWeek_,
                                    false,
                                    false,
                                    $countries
                                );

                                $sub_collect = $sub_collect->get()->toArray();
                                if (count($sub_collect) === 0) {
                                    continue;
                                }

                                $token_search__ = $faker->unique()->uuid;

                                $sub_ = $this->reformat_data(
                                    $sub_collect[0],
                                    $client_id,
                                    $date_to,
                                    $dayOfWeek_,
                                    $check_in,
                                    $current_date,
                                    $to_,
                                    $quantity_child,
                                    $quantity_adults,
                                    $child_min_age,
                                    $language_id,
                                    $quantity_persons,
                                    $supplements_has,
                                    $quantity_total_pax,
                                    $setMarkup,
                                    $token_search__
                                );
                                $sub_['label'] = '[' . $sub_['code'] . '] - ' . $sub_['descriptions']['name_commercial'];
                                $sub_['component_id'] = $substitute->component_id;
                                $sub_['lock'] = $components_[$i_c_]['lock'];
                                $sub_['service_id'] = $substitute->service_id;
                                $sub_['after_days'] = $components_[$i_c_]['after_days'];
                                $sub_['sub_type'] = 'substitute';

                                $substitutes_[$s] = $sub_;
                                $s++;
                            }
                        }
                        $components_[$i_c_]['substitutes'] = $substitutes_;
                        $i_c_++;
                    }

                    // Verificando si el usuario no hizo un reemplazo
                    foreach ($components_ as $k_c => $component_) {
                        $component_client = ComponentClient::where('client_id', $client_id)
                            ->where('component_id', $component_["component_id"]);
                        if ($component_client->count() > 0) {
                            $component_client = $component_client->first();
                            $new_component = [];
                            foreach ($component_['substitutes'] as $c_s_) {
                                if ($component_client->service_id === $c_s_["service_id"]) {
                                    $new_component = $c_s_;
                                    $new_component['lock'] = $component_['lock'];
                                    $new_component['after_days'] = $component_['after_days'];
                                    $new_component['sub_type'] = 'component';
                                    $new_component['substitutes'] = [];

                                    foreach ($component_['substitutes'] as $c_s_) {
                                        if ($c_s_["service_id"] !== $component_client->service_id) {
                                            array_push($new_component['substitutes'], $c_s_);
                                        }
                                    }
                                    // Nuevo substitute
                                    $new_substitute = $component_;
                                    $new_substitute['label'] = '[' . $component_['code'] . '] - ' . $component_['descriptions']['name_commercial'];
                                    $new_substitute['sub_type'] = "substitute";
                                    unset($new_substitute['substitutes']);

                                    array_push($new_component['substitutes'], $new_substitute);
                                    break;
                                }
                            }
                            $components_[$k_c] = $new_component;
                        }
                    }

                    $components = $components_;
                }

                $services[$index_service]['components'] = $components;
                // / COMPONENTES

                //Todo Sacamos el precio minimo y maximo
                if ($index_service === 0) {
                    $min_price_search = $services[$index_service]['total_amount'];
                    $max_price_search = $services[$index_service]['total_amount'];
                } else {
                    //minimo
                    if ($services[$index_service]['total_amount'] < $min_price_search) {
                        $min_price_search = $services[$index_service]['total_amount'];
                    }
                    //maximo
                    if ($services[$index_service]['total_amount'] > $max_price_search) {
                        $max_price_search = $services[$index_service]['total_amount'];
                    }
                }
            } catch (\Exception $e) {
                // Log::debug($e->getMessage());
                return Response::json([
                    'data' => [],
                    'success' => false,
                    'error' => $service_client['id'] . ' - ' . $e->getMessage() . ' - ' . $e->getLine(),
                    'message' => $e->getMessage(),
                    'expiration_token' => '',
                ]);
            }
        }

        if ($recommendations) {
            $services = collect($services)->sortByDesc('rated')->values();
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
            'expiration_token' => $this->expiration_search_services,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reservation_modification(Request $request)
    {
        try {
            $type_request = $request->get('type');
            $reservation_hotel_code = $request->get('reservation_hotel_code');

            $reservation = Reservation::getReservations([
                'reservation_hotel_id' => $reservation_hotel_code,
            ], true);

            $reservation_hotel = $reservation->reservationsHotel[0];

            $client_id = $reservation['client_id'];
            $hotels_id = [$reservation_hotel['hotel_id']];
            $hotel_id = $reservation_hotel['hotel_id'];

            $destinationsRequest = Request::createFrom($request); //$request->toArray()
            $destinationsRequest->request->add([
                'client_id' => $client_id,
                'hotels_id' => $hotels_id,
            ]);

            $destinations = $this->destinations($destinationsRequest);

            $hotelsRequest = Request::createFrom($request); //$request->toArray();
            if ($type_request == 'room_add') {
                $check_in = $reservation_hotel['check_in'];
                $check_out = $reservation_hotel['check_out'];

                $hotelsRequest->request->add([
                    'client_id' => $client_id,
                    'hotels_id' => $hotels_id,
                    'destiny' => [
                        'code' => $destinations->getData()[0]->code,
                        'label' => $destinations->getData()[0]->label,
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

                $quantity_adults = $request->get('adult_num');
                $quantity_child = $request->get('child_num');

                foreach ($result_hotel->hotels as $hotel_ind => $hotel) {
                    if ($hotel->id != $hotel_id) {
                        continue;
                    }

                    $room_request = Request::createFrom($request); //$request->toArray();

                    $rooms_selected = [];
                    foreach ($hotel->rooms as $room_ind => $room) {
                        foreach ($room->rates as $rate) {
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
                                        'quantity_adults' => $quantity_adults,
                                        'quantity_child' => $quantity_child,
                                        'ages_child' => [$request->get('ages_child')],
                                    ],
                                ],
                            ]);

                            $room_response = $this->calculate_selection_rate_total_amount($room_request);

                            if ($room_response->getData()->success == false) {
                                continue;
                            }

                            if (empty($rooms_selected[$room_ind])) {
                                $rooms_selected[$room_ind] = clone $room;
                                $rooms_selected[$room_ind]->rates = [];
                            }

                            $rate_plan_room = $room_response->getData()->rate_plan_rooms[0];

                            $new_rate = clone $rate;

                            $new_rate->rate[0]->amount_days = $rate_plan_room->calendarys;

                            $new_rate->rate[0]->quantity_adults = $rate_plan_room->quantity_adults;
                            $new_rate->rate[0]->quantity_child = $rate_plan_room->quantity_child;
                            $new_rate->rate[0]->quantity_extras = $rate_plan_room->quantity_extras;

                            $new_rate->rate[0]->quantity_adults_total = $rate_plan_room->quantity_adults;
                            $new_rate->rate[0]->quantity_child_total = $rate_plan_room->quantity_child;
                            $new_rate->rate[0]->quantity_extras_total = $rate_plan_room->quantity_extras;

                            $new_rate->rate[0]->total_amount = $rate_plan_room->total_amount;
                            $new_rate->rate[0]->total_taxes_and_services = $rate_plan_room->total_taxes_and_services;
                            $new_rate->rate[0]->calendarys = $rate_plan_room->calendarys;
                            $new_rate->rate[0]->supplements = $rate_plan_room->supplements;
                            $new_rate->rate[0]->policy_cancellation = $rate_plan_room->policy_cancellation;
                            $new_rate->rate[0]->policies_cancellation = $rate_plan_room->policies_cancellation;
                            //
                            $rooms_selected[$room_ind]->rates[] = $new_rate;
                        }
                    }

                    $result_hotel->hotels[$hotel_ind]->rooms = array_values($rooms_selected);
                }

                $repsonse = ['type' => $type_request, 'success' => true, 'data' => $result_hotel];
            } elseif ($type_request == 'date_update') {
                $check_in = Carbon::parse($request->get('check_in'))->format('Y-m-d');
                $check_out = Carbon::parse($request->get('check_out'))->format('Y-m-d');

                $hotelsRequest->request->add([
                    'client_id' => $client_id,
                    'hotels_id' => $hotels_id,
                    'destiny' => [
                        'code' => $destinations->getData()[0]->code,
                        'label' => $destinations->getData()[0]->label,
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

                    $rooms_reserved = [];
                    $rooms_selected = [];
                    foreach ($reservation_hotel->reservationsHotelRooms as $room_reserved) {
                        $rooms_reserved[$room_reserved->rates_plans_room_id][] = [
                            'quantity_adults' => $room_reserved['adult_num'] + (!$room_reserved['extra_num'] ? 0 : $room_reserved['extra_num']),
                            'quantity_child' => $room_reserved['child_num'],
                            'ages_child' => [$request->get('ages_child')],
                        ];
                    }

                    foreach ($rooms_reserved as $rates_plans_room_id => $room_reserved) {
                        foreach ($hotel->rooms as $room_ind => $room) {
                            foreach ($room->rates as $rate) {
                                if ($rates_plans_room_id == $rate->rateId) {
                                    $room_request->request->replace([
                                        'token_search' => $token_search,
                                        'hotel_id' => $hotel_id,
                                        'room_id' => $room->room_id,
                                        'rate_id' => $rate->rateId,
                                        'rate_plan_id' => $rate->ratePlanId,
                                        'date_from' => $check_in,
                                        'date_to' => $check_out,
                                        'client_id' => $client_id,
                                        'rooms' => $room_reserved,
                                    ]);

                                    $room_response = $this->calculate_selection_rate_total_amount($room_request);

                                    if ($room_response->getData()->success == false) {
                                        continue;
                                    }

                                    if (empty($rooms_selected[$room_ind])) {
                                        $rooms_selected[$room_ind] = clone $room;
                                        $rooms_selected[$room_ind]->rates = [];
                                    }

                                    $rate_plan_rooms = $room_response->getData()->rate_plan_rooms;

                                    $new_rate = clone $rate;
                                    $new_rate->rate = [];

                                    $new_rate_2 = clone $rate->rate[0];
                                    foreach ($rate_plan_rooms as $ind => $rate_plan_room) {
                                        $new_rate->rate[$ind] = $new_rate_2;

                                        $new_rate->rate[$ind]->amount_days = $rate_plan_room->calendarys;

                                        $new_rate->rate[$ind]->quantity_adults = $rate_plan_room->quantity_adults;
                                        $new_rate->rate[$ind]->quantity_child = $rate_plan_room->quantity_child;
                                        $new_rate->rate[$ind]->quantity_extras = $rate_plan_room->quantity_extras;

                                        $new_rate->rate[$ind]->quantity_adults_total = $rate_plan_room->quantity_adults;
                                        $new_rate->rate[$ind]->quantity_child_total = $rate_plan_room->quantity_child;
                                        $new_rate->rate[$ind]->quantity_extras_total = $rate_plan_room->quantity_extras;
                                        $new_rate->rate[$ind]->total_amount = $rate_plan_room->total_amount;
                                        $new_rate->rate[$ind]->total_taxes_and_services = $rate_plan_room->total_taxes_and_services;

                                        $new_rate->rate[$ind]->calendarys = $rate_plan_room->calendarys;
                                        $new_rate->rate[$ind]->supplements = $rate_plan_room->supplements;
                                        $new_rate->rate[$ind]->policy_cancellation = $rate_plan_room->policy_cancellation;
                                        $new_rate->rate[$ind]->policies_cancellation = $rate_plan_room->policies_cancellation;
                                    }

                                    $new_rate->quantity_rates = count($new_rate->rate);
                                    $new_rate->political = $room_response->getData()->political;
                                    //
                                    $rooms_selected[$room_ind]->rates[] = $new_rate;

                                    break 2;
                                }
                            }
                        }
                    }

                    $result_hotel->hotels[$hotel_ind]->rooms = array_values($rooms_selected);
                }

                $repsonse = ['type' => $type_request, 'success' => true, 'data' => $result_hotel];
            } elseif ($type_request == 'room_delete') {

                $request_res = [
                    'type' => 'room_delete',
                    'reservation_hotel_code' => 110,
                    'reservation_hotel_room_code' => 265,
                ];
                $repsonse = ['type' => $type_request, 'success' => true, 'data' => $request_res];
            }
        } catch (\Exception $e) {
            $repsonse = ['success' => false, 'error' => $e->getMessage()];
        }

        return Response::json($repsonse);
    }

    public function reservation_package(Request $request)
    {
        $client_id = $request->input('client_id');
        $package_name = $request->input('package_name');
        $passengers = $request->input('passengers');

        $object_id = $request->input('object_id');

        $quantity_rooms_sgl = $request->input('quantity_rooms_sgl');
        $quantity_rooms_dbl = $request->input('quantity_rooms_dbl');
        $quantity_rooms_tpl = $request->input('quantity_rooms_tpl');

        $reference = ($request->has('reference')) ? $request->input('reference') : '';

        $packages_selected = Cache::get('packages_selected_' . Auth::user()->id);

        //cantidad real de pax
        $quantity_adults = $packages_selected["params"]["quantity_persons"][0]["adults"];
        $quantity_child = ($packages_selected["params"]["quantity_persons"][0]["child_with_bed"] + $packages_selected["params"]["quantity_persons"][0]["child_without_bed"]);

        $reservation_data = [
            "client_id" => $client_id,
            "file_code" => "",
            "reference" => $package_name,
            "reference_new" => $reference,
            "guests" => [
                [
                    "given_name" => "Pasajero 1",
                    "surname" => "Pasajero 1",
                ],
            ],
            "reservations" => [],
            "reservations_services" => [],
            "reservations_flights" => [],
            "date_init" => $packages_selected["params"]["date"],
            "entity" => 'Package',
            "object_id" => $object_id,
        ];

        if (Auth::user()->user_type_id == 3) {
            $reservation_data['executive_id'] = Auth::user()->id;
        }

        $reservation_passengers = [];
        if (!empty($passengers) and count($passengers) > 0) {
            foreach ($passengers as $passenger) {
                $date_birth = Carbon::createFromFormat('d/m/Y', $passenger["fecnac"])->format('Y-m-d');
                array_push($reservation_passengers, [
                    'doctype_iso' => (isset($passenger["tipdoc"])) ? $passenger["tipdoc"] : null,
                    'document_number' => $passenger["nrodoc"],
                    'given_name' => $passenger["nombres"],
                    'surname' => $passenger["apellidos"],
                    'date_birth' => $date_birth,
                    'genre' => $passenger["sexo"],
                    'type' => $passenger["tipo"],
                    'phone' => $passenger["celula"],
                    'dietary_restrictions' => $passenger["resali"],
                    'medical_restrictions' => $passenger["resmed"],
                    'notes' => $passenger["observ"],
                    'country_iso' => $passenger["nacion"],
                    'email' => $passenger["correo"],
                ]);
            }
            $reservation_data["guests"] = $reservation_passengers;
        }
        foreach ($packages_selected["package"] as $package_selected) {
            if ($package_selected != null) {
                foreach ($package_selected["services"] as $service) {
                    try {
                        if ($service["type"] == "hotel") {
                            foreach ($service["service_rooms"] as $index => $service_room) {
                                try {
                                    $rate_plan_room_model = RatesPlansRooms::find($service_room["rate_plan_room_id"]);
                                    $room_model = Room::find($rate_plan_room_model->room_id);
                                    $room_type = RoomType::find($room_model->room_type_id);
                                    $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                                } catch (\Exception $e) {
                                    return Response::json([
                                        'success' => false,
                                        'error' => 'No existe rate plan rooms id:' . $service_room["rate_plan_room_id"],
                                    ]);
                                }
                            }
                            //return response()->json($service["service_rooms"][0]["rate_plan_room_id"]);
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
                                        'label' => $destinations->getData()[0]->label,
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
                                                                    "child_ages" => [],
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
                                                                    "child_ages" => [],
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
                                                                    "child_ages" => [],
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
                                'client_id' => (int)$client_id,
                                'origin' => [
                                    'code' => $destinations->getData()->data->origins[0]->code,
                                    'label' => $destinations->getData()->data->origins[0]->label,
                                ],
                                'destiny' => [
                                    'code' => $destinations->getData()->data->destinations[0]->code,
                                    'label' => $destinations->getData()->data->destinations[0]->label,
                                ],
                                'lang' => 'es',
                                'date' => $check_in,
                                'services_id' => $services_id,
                                'supplements' => false,
                                'quantity_persons' => [
                                    "adults" => (int)$quantity_adults,
                                    "child" => (int)$quantity_child,
                                    "age_childs" => [
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
                                'rate_plan_id' => $result_service->services[0]->rate->id,
                                'reservation_time' => empty($result_service->services[0]->reservation_time) ? '00:00' : $result_service->services[0]->reservation_time,
                                'date_from' => $check_in,
                                "guest_note" => "",
                                'quantity_adults' => $quantity_adults,
                                'quantity_child' => $quantity_child,
                                'child_ages' => [],
                            ]);
                        }

                        if ($service["type"] == "flight") {
                            $services_id = [$service["object_id"]];
                            $service_id = $service["object_id"];
                            $check_in = Carbon::createFromFormat('d/m/Y', $service["date_in"])->format('Y-m-d');

                            array_push($reservation_data["reservations_flights"], [
                                'service_id' => $service_id,
                                'client_id' => (int)$client_id,
                                'origin' => $service["origin"],
                                'destiny' => $service["destiny"],
                                'code_flight' => $service["code_flight"],
                                'lang' => 'es',
                                'date' => $check_in,
                                'services_id' => $services_id,
                                'quantity_persons' => [
                                    "adults" => (int)$service["adult"],
                                    "child" => (int)$service["child"],
                                    "ages_child" => [
                                        [
                                            'child' => 1,
                                            'age' => 1,
                                        ],
                                    ],
                                ],
                            ]);
                        }
                    } catch (\Exception $e) {
                        return Response::json([
                            'success' => false,
                            'message' => 'object_id: ' . $service["object_id"] . ' type : ' . $service["type"],
                            'error' => $e->getMessage() . ' - Line:' . $e->getLine(),
                        ]);
                    }
                }
            }
        }

        return response()->json($reservation_data, 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserve(Request $request)
    {
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $reference = $request->input('reference');

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

                $reservation_data = [
                    "client_id" => $client_id,
                    "executive_id" => Auth::user()->id,
                    "file_code" => "",
                    "reference" => $package_name,
                    "reference_new" => $reference,
                    "guests" => [
                        [
                            "given_name" => "Pasajero 1",
                            "surname" => "Pasajero 1",
                        ],
                    ],
                    "reservations" => [],
                    "reservations_services" => [],
                    "date_init" => $date,
                ];

                $services = $this->getServicesByPackage($package_plan_rate_category_id, $lang);
                $array_services_new = $this->updateDateInServices($services, $date, false);
                $services = $array_services_new["services"];
                $services = $this->getHotelsWithStatus($services, $date);
                foreach ($services as $service) {
                    try {
                        if ($service["type"] == "hotel") {
                            foreach ($service["service_rooms"] as $index => $service_room) {
                                $rate_plan_room_model = RatesPlansRooms::find($service_room["rate_plan_room_id"]);
                                $room_model = Room::find($rate_plan_room_model->room_id);
                                $room_type = RoomType::find($room_model->room_type_id);

                                $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                            }
                            //return response()->json($service["service_rooms"][0]["rate_plan_room_id"]);
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
                                        'label' => $destinations->getData()[0]->label,
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
                                                                    "child_ages" => [],
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
                                                                    "child_ages" => [],
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
                                                                    "child_ages" => [],
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
                                'client_id' => (int)$client_id,
                                'origin' => [
                                    'code' => $destinations->getData()->data->origins[0]->code,
                                    'label' => $destinations->getData()->data->origins[0]->label,
                                ],
                                'destiny' => [
                                    'code' => $destinations->getData()->data->destinations[0]->code,
                                    'label' => $destinations->getData()->data->destinations[0]->label,
                                ],
                                'lang' => 'es',
                                'date' => $check_in,
                                'services_id' => $services_id,
                                'quantity_persons' => [
                                    "adults" => (int)$quantity_adults,
                                    "child" => (int)$quantity_child,
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
                                'rate_plan_id' => $result_service->services[0]->rate->id,
                                'reservation_time' => empty($result_service->services[0]->reservation_time) ? '00:00' : $result_service->services[0]->reservation_time,
                                'date_from' => $check_in,
                                "guest_note" => "",
                                'quantity_adults' => $quantity_adults,
                                'quantity_child' => $quantity_child,
                                'child_ages' => [],
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

                $reservation_data = ServiceRequest::createFrom($reservation_data);

                $response = $this->reservationPush($reservation_data);
            }
        }

        return Response::json($response);
    }

    public function reservation_quote(Request $request)
    {
        try {

            Log::info('reservation_quote:start', [
                'client_id' => $request->input('client_id'),
                'quote_id' => $request->input('quote_id'),
                'quote_category_id' => $request->input('quote_category_id'),
                'file_code' => $request->input('file_code')
            ]);

            $client_id = $request->input('client_id');
            $quote_id = $request->input('quote_id');
            $quote_id_original = $request->input('quote_id_original');
            $file_code = $request->has('file_code') ? $request->input('file_code') : '';
            $quote_category_id = $request->input('quote_category_id');
            $services_optionals = collect($request->input('services_optionals'));
            $quote = Quote::find($quote_id);
            $quote_categories = QuoteCategory::where('quote_id', $quote_id)
                ->where('id', $quote_category_id)
                ->get();
            Log::info('quote_categories', [
                'count' => $quote_categories->count(),
                'data' => $quote_categories->toArray()
            ]);

            if ($quote_categories->isEmpty()) {
                Log::error('quote_category_not_found', [
                    'quote_id' => $quote_id,
                    'quote_category_id' => $quote_category_id
                ]);

                return Response::json([
                    'success' => false,
                    'error' => 'Quote category not found'
                ], 404);
            }

            $quote_passengers = QuotePassenger::where('quote_id', $quote_id)->get();
            $billing = [];
            //Todo Validamos que el file exista en caso aya informacion
            if (!empty($file_code)) {
                $findFile = Reservation::where('file_code', $file_code)
                    ->where('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CLOSE_PROCESS)
                    ->limit(1)
                    ->get(['id', 'file_code', 'customer_name']);
                if ($findFile->count() === 0) {
                    return Response::json([
                        'success' => false,
                        'error' => trans('validations.reservations.file_number_not_found', ['file' => $file_code]),
                    ]);
                }
            } else {
                $reservation_quote = Reservation::where('entity', 'Quote')
                    ->where('object_id', $quote_id_original)
                    ->first(['id', 'entity', 'object_id', 'file_code']);
                if ($reservation_quote) {
                    return Response::json([
                        'success' => false,
                        'error' => trans('validations.reservations.quote_number_already_has_a_file_assigned', [
                            'quote_id' => $quote_id_original,
                            'file_code' => $reservation_quote->file_code,
                        ]),
                    ]);
                }
            }

            //Todo Validamos que la cotizacion tenga hoteles o servicios
            $validate_services = QuoteService::where('quote_category_id', $quote_categories[0]['id'])
                ->whereIn('type', ['service', 'hotel'])
                ->orderBy('date_in')
                ->orderBy('order')
                ->count();
            if ($validate_services === 0) {
                return Response::json([
                    'success' => false,
                    'error' => trans('validations.quotes.quote_must_have_at_least_hotel_or_service'),
                ]);
            }
            $quote_has_children = 0;
            if ($quote_passengers->count() > 0) {
                $guests = [];

                //Todo Buscamos al cliente para saber si tiene permisos de poder crear un pasajero directo
                $allow_create_cliente = Client::where('id', $client_id)
                    ->where('allow_direct_passenger_creation', 1)
                    ->first(['allow_direct_passenger_creation']);
                if ($allow_create_cliente) {
                    //Todo Verificamos si el primer pasajero tiene activado la opcion de Cliente Directo
                    $paxClientDirect = $quote_passengers->where('is_direct_client', true)->first();
                    if ($paxClientDirect) {
                        $docType = Doctype::where('iso', $paxClientDirect['doctype_iso'])->first(['id']);
                        $country = Country::where('iso', $paxClientDirect['country_iso'])->first(['id']);
                        $document_type_id = 3;
                        $country_id = 89;
                        if ($docType) {
                            $document_type_id = $docType['id'];
                        }

                        if ($country) {
                            $country_id = $country['id'];
                        }

                        $billing = [
                            'name' => $paxClientDirect['first_name'],
                            'surnames' => $paxClientDirect['last_name'],
                            'phone' => $paxClientDirect['phone'],
                            'email' => $paxClientDirect['email'],
                            'document_type_id' => $document_type_id,
                            'document_number' => $paxClientDirect['document_number'],
                            'address' => $paxClientDirect['address'],
                            'country_id' => $country_id,
                            'state_iso' => $paxClientDirect['city_ifx_iso'],
                        ];
                    }
                }

                //Todo Buscamos si hay un niño
                foreach ($quote_passengers as $passenger) {
                    if ($passenger["type"] == 'CHD') {
                        $quote_has_children++;
                    }
                }

                //Todo Agregamos los pasajeros a la reserva
                foreach ($quote_passengers as $passenger) {
                    if ($passenger["type"] == 'CHD' && empty($passenger["birthday"])) {
                        return Response::json([
                            'success' => false,
                            'error' => 'Debe ingresar la fecha de nacimiento de los niños en la lista de pasajeros',
                        ]);
                        die;
                    }

                    $date_birth = null;
                    if (isset($passenger['birthday'])) {

                        $date_birth = $passenger['birthday'];
                        $dateIsInvalid = Validator::make(['date' => $date_birth], ['date' => 'date_format:Y-m-d'])->fails();
                        if ($dateIsInvalid) {
                            $date_birth = null;
                        }
                    }

                    if ($date_birth !== null) {

                        if ($passenger['type'] == 'ADL') {
                            $tempDateBirth = Carbon::parse($date_birth);
                            $isUnderdage = Carbon::now()->diffInYears($tempDateBirth) < 18;
                            if ($isUnderdage) {
                                $date_birth = null;
                            }
                        }

                        if ($passenger['type'] == 'CHD') {
                            $tempDateBirth = Carbon::parse($date_birth);
                            $isUnderdage = Carbon::now()->diffInYears($tempDateBirth) > 17;
                            if ($isUnderdage) {

                                return Response::json([
                                    'success' => false,
                                    'error' => 'La edad del niño no es valida : ' . $date_birth,
                                ]);
                                die;
                            }
                        }
                    }

                    array_push($guests, [
                        'doctype_iso' => (isset($passenger["doctype_iso"])) ? $passenger["doctype_iso"] : null,
                        'document_number' => $passenger["document_number"],
                        'given_name' => $passenger["first_name"],
                        'surname' => $passenger["last_name"],
                        'date_birth' => $date_birth,
                        'genre' => $passenger["gender"],
                        'type' => $passenger["type"],
                        'phone' => $passenger["phone"],
                        'dietary_restrictions' => $passenger["dietary_restrictions"],
                        'medical_restrictions' => $passenger["medical_restrictions"],
                        'notes' => $passenger["notes"],
                        'country_iso' => $passenger["country_iso"],
                        'city_iso' => $passenger["city_ifx_iso"],
                        'email' => $passenger["email"],
                        'document_url' => $passenger["document_url"]
                    ]);
                }
            } else {
                $guests = [
                    [
                        "given_name" => "",
                        "surname" => "",
                    ],
                ];
            }

            $reservation_data = [
                "client_id" => $client_id,
                "file_code" => $file_code,
                "reference" => $quote->name,
                "guests" => $guests,
                "reservations" => [],
                "billing" => $billing,
                "reservations_services" => [],
                "reservations_flights" => [],
                "date_init" => "",
                "set_markup" => $quote->markup,
            ];

            if (Auth::user()->user_type_id == 3) {
                $reservation_data['executive_id'] = Auth::user()->id;
            }

            $validation_errors = collect([
                'hotels' => collect(),
                'services' => collect(),
            ]);

            $services_age_children = [];
            $child_min_age = 0;
            //Todo si hay niños sacamos las edades para los servicios
            if ($quote_has_children > 0) {
                $quote_ages_child = QuoteAgeChild::where('quote_id', $quote_id)->get(['age']);
                if ($quote_ages_child->count() > 0) {
                    foreach ($quote_ages_child as $key => $age) {

                        if ($child_min_age === 0) {
                            $child_min_age = (int)$age["age"];
                        }
                        if ($age["age"] < $child_min_age) {
                            $child_min_age = (int)$age["age"];
                        }

                        $services_age_children[] = [
                            'child' => $key + 1,
                            'age' => $age['age'],
                        ];
                    }
                } else {
                    return Response::json([
                        'success' => false,
                        'error' => 'Debe ingresar las edades de los niños',
                    ]);
                    die;
                }
            } else {
                $services_age_children[] = [
                    'child' => 1,
                    'age' => 1,
                ];
            }

            //Todo Fecha de inicio del file
            $quote_services_init = QuoteService::where('quote_category_id', $quote_categories[0]['id'])
                ->orderBy('date_in')
                ->orderBy('order')
                ->first();

            $reservation_data["date_init"] = Carbon::createFromFormat(
                'd/m/Y',
                $quote_services_init->date_in
            )->format('Y-m-d');

        foreach ($quote_categories as $quote_category) {
            $quote_services = QuoteService::where('quote_category_id', $quote_category["id"])
                ->where('locked', 0)
                ->with('service_rate')
                ->with('service_rooms')
                ->with('service_rooms_hyperguest.room.room_type')
                ->orderBy('date_in')
                ->orderBy('order')
                ->get();





            foreach ($quote_services as $_i => $service) {

                $setMarkupReservation = ($quote->markup > 0 ? $quote->markup : $service["markup_regionalization"]);
                //Todo Veficicamos si es un opcional
                if ($service->optional == 1) {
                    //Todo Veficicamos si hay opcionales para ser reservados
                    if ($services_optionals->count() > 0) {
                        /*
                         * Todo Veficicamos en la lista de servicios opcionales si se encuentra el servicio para ser reservado
                         *  de lo contrario obviara el servicio y continuara con el siguiente
                         */
                        if ($service->type === "hotel") {
                            $search_option_reservation = $services_optionals->where('object_id', $service->object_id)
                                ->where('date_in', $service->date_in)->where('date_out', $service->date_out);
                        } else {
                            $search_option_reservation = $services_optionals->where('object_id', $service->object_id)
                                ->where('date_in', $service->date_in);
                        }

                            if ($search_option_reservation->count() === 0) {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }

                if ($service["type"] == "hotel") {
                    $total_rooms = (int)$service["single"] + (int)$service["double"] + (int)$service["triple"] + (int)$service["double_child"] + (int)$service["triple_child"];
                    if ($total_rooms > 0) {



                        $verify_available_room = [
                            'simple' => false,
                            'double' => false,
                            'triple' => false,
                        ];
                        foreach ($service["service_rooms"] as $index => $service_room) {
                            try {
                                $rate_plan_room_model = RatesPlansRooms::find($service_room["rate_plan_room_id"]);
                                $room_model = Room::find($rate_plan_room_model->room_id);
                                $room_type = RoomType::find($room_model->room_type_id);
                                $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                            } catch (\Exception $e) {
                                $hotel_validate = Hotel::where('id', $service['object_id'])
                                    ->withTrashed()
                                    ->with([
                                        'channels' => function ($query) {
                                            $query->where('state', 1);
                                        },
                                    ])
                                    ->first(['id']);
                                return Response::json([
                                    'success' => false,
                                    'errors' => [
                                        'hotels' => [
                                            [
                                                'hotel_code' => $hotel_validate->channels[0]->pivot->code,
                                                "name" => $hotel_validate->name,
                                                'errors' => [
                                                    [
                                                        'error' => trans(
                                                            'validations.quotes.hotels.hotel_rate_deleted',
                                                            ['room_id' => $service_room["rate_plan_room_id"]]
                                                        ),
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ]);
                            }
                        }

                            $hotels_id = [$service["object_id"]];
                            $hotel_id = $service["object_id"];

                            $check_in = Carbon::createFromFormat('d/m/Y', $service["date_in"])->format('Y-m-d');
                            $check_out = Carbon::createFromFormat('d/m/Y', $service["date_out"])->format('Y-m-d');

                            $checkInValidate = Carbon::parse($check_in);
                            $checkInNights = $checkInValidate->diffInDays($check_out);

                            if ($checkInNights != intval($service["nights"])) {

                                $hotel_validate = Hotel::where('id', $service['object_id'])->withTrashed()
                                    ->with([
                                        'channels' => function ($query) {
                                            $query->where('state', 1);
                                        },
                                    ])->first(['id', 'name']);

                                $validations = collect([
                                    "hotel_code" => $hotel_validate->channels[0]->pivot->code,
                                    "name" => $hotel_validate->name,
                                    "errors" => collect()->add([
                                        "error" => trans('validations.quotes.hotels.hotel_check_in_check_out'),
                                    ]),
                                ]);

                                $validation_errors['hotels']->add($validations);

                                return Response::json(['success' => false, 'errors' => $validation_errors]);
                            }

                            $destinationsRequest = Request::createFrom($request);

                            $destinationsRequest->request->add([
                                'client_id' => $client_id,
                                'hotels_id' => $hotels_id,
                            ]);

                            $destinations = $this->destinations($destinationsRequest);

                            Log::info('destinations_response', [
                                'raw' => $destinations->getContent()
                            ]);

                            $destinationsData = json_decode($destinations->getContent(), true);

                            if (empty($destinationsData)) {
//                        if (count($destinations->getData()) === 0) {
                                $validation_errors['hotels']->add($this->validationsQuoteHotels(
                                    $service,
                                    $client_id,
                                    $check_in,
                                    $service["service_rooms"]
                                ));
                                continue;
                            }

                        $hotelsRequest = Request::createFrom($request);
                        // $hotelsRequest->request->add();
                        $paramsRequest = [
                            'client_id' => $client_id,
                            'hotels_id' => $hotels_id,
                            'destiny' => [
                                'code' => $destinations->getData()[0]->code,
                                'label' => $destinations->getData()[0]->label,
                            ],
                            'date_from' => $check_in,
                            'date_to' => $check_out,
                            'typeclass_id' => 'all',
                            'quantity_rooms' => 1,
                            'set_markup' => $setMarkupReservation,
                            'quantity_persons_rooms' => [
                                [
                                    "room" => 1,
                                    "adults" => $service["adult"],
                                    "child" => $service["child"],
                                    "ages_child" => $services_age_children,
                                ],
                            ],
                        ];

                        $hotelSearchRequest = $this->convertToHotelSearchRequest($paramsRequest);
                        $serviceControllerAvailable = new ServiceControllerAvailable();
                        $hotel_search = $serviceControllerAvailable->hotels_channels($hotelSearchRequest);

                            if (isset($hotel_search->getData()->data)) {
                                $result_hotel = $hotel_search->getData()->data[0]->city;
                            } else {
                                $result_hotel = (object)['hotels' => []];
                            }

                            Log::info('hotel_search_result', [
                                'hotel_search' => $hotel_search->getContent()
                            ]);
                            if (empty($result_hotel->hotels)) {
//                        if (count($result_hotel->hotels) === 0) {
                                $validation_errors['hotels']->add($this->validationsQuoteHotels(
                                    $service,
                                    $client_id,
                                    $check_in,
                                    $service["service_rooms"]
                                ));
                                continue;
                            }

                        if (!$service["hyperguest_pull"]) {

                            foreach ($result_hotel->hotels as $hotel_ind => $hotel) {
                                if ($hotel->id != $hotel_id) {
                                    continue;
                                }

                                $room_ident = 0;
                                //Todo: Reserva para Simple
                                foreach ($service["service_rooms"] as $service_room) {
                                    if ($service_room["occupation"] == 1) {
                                        if ($service["single"] > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                foreach ($room->rates as $rate) {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        $verify_available_room['simple'] = true;
                                                        array_push($reservation_data["reservations"], [
                                                            "token_search" => $result_hotel->token_search,
                                                            "room_ident" => $room_ident,
                                                            "hotel_id" => $hotel_id,
                                                            "notes" => $service["notes"],
                                                            "best_option" => false,
                                                            "rate_plan_room_id" => $rate->rateId,
                                                            "suplements" => [],
                                                            "guest_note" => "",
                                                            "date_from" => $check_in,
                                                            "date_to" => $check_out,
                                                            "quantity_adults" => $service["adult"],
                                                            "quantity_child" => $service["child"],
                                                            "optional" => $service['optional'],
                                                            "child_ages" => $services_age_children,
                                                            "set_markup" => $setMarkupReservation
                                                        ]);
                                                        $room_ident++;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                //Todo: Reserva para Doble
                                foreach ($service["service_rooms"] as $service_room) {
                                    if ($service_room["occupation"] == 2) {
                                        if ($service["double"] > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                foreach ($room->rates as $rate) {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        $verify_available_room['double'] = true;
                                                        array_push($reservation_data["reservations"], [
                                                            "token_search" => $result_hotel->token_search,
                                                            "room_ident" => $room_ident,
                                                            "hotel_id" => $hotel_id,
                                                            "notes" => $service["notes"],
                                                            "best_option" => false,
                                                            "rate_plan_room_id" => $rate->rateId,
                                                            "suplements" => [],
                                                            "guest_note" => "",
                                                            "date_from" => $check_in,
                                                            "date_to" => $check_out,
                                                            "quantity_adults" => $service["adult"],
                                                            "quantity_child" => $service["child"],
                                                            "optional" => $service['optional'],
                                                            "child_ages" => $services_age_children,
                                                            "set_markup" => $setMarkupReservation
                                                        ]);
                                                        $room_ident++;

                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                //Todo: Reserva para Triple
                                foreach ($service["service_rooms"] as $service_room) {
                                    if ($service_room["occupation"] == 3) {
                                        if ($service["triple"] > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                foreach ($room->rates as $rate) {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        $verify_available_room['triple'] = true;
                                                        array_push($reservation_data["reservations"], [
                                                            "token_search" => $result_hotel->token_search,
                                                            "room_ident" => $room_ident,
                                                            "hotel_id" => $hotel_id,
                                                            "notes" => $service["notes"], // reservation_hotels
                                                            "best_option" => false,
                                                            "rate_plan_room_id" => $rate->rateId,
                                                            "suplements" => [],
                                                            "guest_note" => "",
                                                            "date_from" => $check_in,
                                                            "date_to" => $check_out,
                                                            "quantity_adults" => $service["adult"],
                                                            "quantity_child" => $service["child"],
                                                            "optional" => $service['optional'],
                                                            "child_ages" => $services_age_children,
                                                            "set_markup" => $setMarkupReservation
                                                        ]);
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $ratePlanRoomIds = collect($service["service_rooms"] ?? [])
                                ->pluck('rate_plan_room_id')
                                ->filter()
                                ->values()
                                ->all();

                            // Obtener channel_id y status desde RatesPlansRooms (1=Aurora, 6=Hyperguest)
                            $ratesInfo = RatesPlansRooms::whereIn('id', $ratePlanRoomIds)
                                ->get(['channel_id', 'status']);

                            $channelIds = $ratesInfo->pluck('channel_id')->unique()->values()->all();
                            $statuses = $ratesInfo->pluck('status')->unique()->values()->all();

                            $channelLabel = 'AURORA'; // default seguro
                            if (in_array(6, $channelIds, true)) {
                                // Si hay al menos una tarifa Hyperguest en la selección, lo indicamos (puede coexistir)
                                $channelLabel = (in_array(1, $channelIds, true)) ? 'AURORA+HYPERGUEST' : 'HYPERGUEST';
                            }

                            $context = [
                                'check_in' => $check_in,
                                'check_out' => $check_out,
                                'nights' => $service["nights"] ?? null,
                                'channel' => $channelLabel,
                                'rate_plan_room_ids' => $ratePlanRoomIds,
                                'on_request' => in_array(0, $statuses, true),
                            ];

                            $validate_rooms = $this->validateRoomsHotelV2($service, $verify_available_room, $context);

                            if ($validate_rooms->count() > 0) {
                                $validation_errors['hotels']->add($validate_rooms);
                            }

                        }else{

                            foreach ($result_hotel->hotels as $hotel_ind => $hotel) {
                                if ($hotel->id != $hotel_id) {
                                    continue;
                                }

                                $room_ident = 0;
                                //Todo: Reserva para Simple
                                foreach ($service["service_rooms_hyperguest"] as $service_room) {
                                    if ($service_room["room"]["room_type"]["occupation"] == 1) {
                                        if ($service["single"] > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {

                                                if($room->room_id == $service_room['room_id'])
                                                {
                                                    foreach ($room->rates as $rate) {
                                                        if ($rate->ratePlanId == $service_room["rate_plan_id"]) {
                                                            $verify_available_room['simple'] = true;
                                                            array_push($reservation_data["reservations"], [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => $room_ident,
                                                                "hotel_id" => $hotel_id,
                                                                "notes" => $service["notes"],
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "quantity_adults" => $service["adult"],
                                                                "quantity_child" => $service["child"],
                                                                "optional" => $service['optional'],
                                                                "child_ages" => $services_age_children,
                                                                "set_markup" => $setMarkupReservation
                                                            ]);
                                                            $room_ident++;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                //Todo: Reserva para Doble
                                foreach ($service["service_rooms_hyperguest"] as $service_room) {
                                    if ($service_room["room"]["room_type"]["occupation"] == 2) {
                                        if ($service["double"] > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                if($room->room_id == $service_room['room_id'])
                                                {
                                                    foreach ($room->rates as $rate) {
                                                        if ($rate->ratePlanId == $service_room["rate_plan_id"]) {
                                                            $verify_available_room['double'] = true;
                                                            array_push($reservation_data["reservations"], [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => $room_ident,
                                                                "hotel_id" => $hotel_id,
                                                                "notes" => $service["notes"],
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "quantity_adults" => $service["adult"],
                                                                "quantity_child" => $service["child"],
                                                                "optional" => $service['optional'],
                                                                "child_ages" => $services_age_children,
                                                                "set_markup" => $setMarkupReservation
                                                            ]);
                                                            $room_ident++;

                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                //Todo: Reserva para Triple
                                foreach ($service["service_rooms_hyperguest"] as $service_room) {
                                    if ($service_room["room"]["room_type"]["occupation"] == 3) {
                                        if ($service["triple"] > 0) {
                                            foreach ($hotel->rooms as $room_ind => $room) {
                                                if($room->room_id == $service_room['room_id'])
                                                {
                                                    foreach ($room->rates as $rate) {
                                                        if ($rate->ratePlanId == $service_room["rate_plan_id"]) {
                                                            $verify_available_room['triple'] = true;
                                                            array_push($reservation_data["reservations"], [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => $room_ident,
                                                                "hotel_id" => $hotel_id,
                                                                "notes" => $service["notes"], // reservation_hotels
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "quantity_adults" => $service["adult"],
                                                                "quantity_child" => $service["child"],
                                                                "optional" => $service['optional'],
                                                                "child_ages" => $services_age_children,
                                                                "set_markup" => $setMarkupReservation
                                                            ]);
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                        }
                    }


                }

                    if ($service["type"] == "service") {

                        $services_id = [$service["object_id"]];
                        $service_id = $service["object_id"];

                        $check_in = Carbon::createFromFormat('d/m/Y', $service["date_in"])->format('Y-m-d');

                        $destinationsRequest = Request::createFrom($request);

                        $destinationsRequest->request->add([
                            'client_id' => $client_id,
                            'services_id' => $services_id,
                            'with_period' => Carbon::parse($check_in)->year,
                        ]);

                    $servicesRequest = ServiceRequest::createFrom($request);
                    //
                    $servicesRequest->request->add([
                        'client_id' => (int)$client_id,
                        'lang' => 'es',
                        'supplements' => false,
                        'date' => $check_in,
                        'services_id' => $services_id,
                        'set_markup' => $quote->markup,
                        'quantity_persons' => [
                            "adults" => (int)$service["adult"],
                            "child" => (int)$service["child"],
                            "age_childs" => $services_age_children,
                        ],
                        'set_markup' => $setMarkupReservation
                    ]);
                    $service_search = $this->services($servicesRequest);

                        if (isset($service_search->getData()->data)) {
                            $result_service = $service_search->getData()->data;
                        } else {
                            $result_service = (object)['services' => []];
                        }

                        $response = json_decode($service_search->getContent(), true);
                        if ($response['success'] == false) {
                            return Response::json([
                                'success' => false,
                                "message" => $response['message'],
                                "error" => $response['error'],
                            ]);
                            die;
                        }

                    if (count($result_service->services) == 0) {
                        $validation_errors['services']->add($this->validationsQuoteServices(
                            $service,
                            $client_id,
                            $services_age_children
                        ));
                        continue;
                    }

                        $token_search = $result_service->token_search;

                        // Todo obtener segun el schedule elegido la hora de inicio
                        $try_get_schedule_from_results = false;
                        if ($service["hour_in"] != "" && $service["hour_in"] != null) {
                            $reservation_time_ = $service["hour_in"];
                        } else {
                            $reservation_time_ = $this->get_hour_ini(
                                $service["schedule_id"],
                                $check_in,
                                $service["object_id"]
                            );
                            if ($reservation_time_ == null || $reservation_time_ == '') {
                                $try_get_schedule_from_results = true;
                            }
                        }
                        if ($try_get_schedule_from_results) {
                            $reservation_time_ = empty($result_service->services[0]->reservation_time) ? '00:00' : $result_service->services[0]->reservation_time;
                        }

                        array_push($reservation_data["reservations_services"], [
                            'token_search' => $token_search,
                            'service_id' => $service_id,
                            'service_ident' => 0,
                            'rate_plan_id' => $result_service->services[0]->rate->id, // *
                            'reservation_time' => $reservation_time_,
                            'date_from' => $check_in,
                            "guest_note" => "",
                            'quantity_adults' => $service["adult"],
                            'quantity_child' => $service["child"],
                            "optional" => $service->optional,
                            'child_ages' => $services_age_children,
                        ]);
                    }

                    if ($service["type"] == "flight") {
                        $services_id = [$service["object_id"]];
                        $service_id = $service["object_id"];
                        $check_in = Carbon::createFromFormat('d/m/Y', $service["date_in"])->format('Y-m-d');

                    array_push($reservation_data["reservations_flights"], [
                        'service_id' => $service_id,
                        'client_id' => (int)$client_id,
                        'origin' => $service["origin"],
                        'destiny' => $service["destiny"],
                        'code_flight' => $service["code_flight"],
                        'lang' => 'es',
                        'date' => $check_in,
                        'services_id' => $services_id,
                        'quantity_persons' => [
                            "adults" => (int)$service["adult"],
                            "child" => (int)$service["child"],
                            "ages_child" => [
                                [
                                    'child' => 1,
                                    'age' => 1,
                                ],
                            ],
                        ],
                        'set_markup' => $setMarkupReservation
                    ]);
                }
            }
        }

            if ($validation_errors['hotels']->count() > 0 or $validation_errors['services']->count() > 0) {
                return Response::json(['success' => false, 'errors' => $validation_errors]);
            }

            return Response::json(['success' => true, 'response' => $reservation_data]);

        } catch (\Throwable $e) {

            Log::error('reservation_quote_exception', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancellation_packages(Request $request)
    {
        return $this->do_cancellation($request, 'package');
    }

    public function cancellation_services(Request $request)
    {
        return $this->do_cancellation($request, 'service');
    }

    public function do_cancellation($request, $entity)
    {
        try {
            $arrayErrors = [];
            $countErrors = 0;
            $validator = Validator::make($request->all(), [
                'file_code' => 'required|unique:tickets,file_code|exists:reservations,file_code',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {

                    array_push($arrayErrors, $error);
                }
                $countErrors++;
            }
            DB::beginTransaction();
            if ($countErrors > 0) {
                return Response::json(['success' => false, 'message' => $errors], 422);
            } else {
                $ticket = new Ticket();
                $ticket->type = 'file';
                $ticket->file_code = $request->input('file_code');
                $ticket->origin = 'API';
                $ticket->action = 'cancellation';
                $ticket->status = 0;
                $ticket->entity = $entity;
                $ticket->save();
                $this->sendNotificationTicket($ticket);
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, "message" => $e]);
        }
    }

    /**
     * Store a newly created cancellation file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function cancellation_service_file(Request $request)
    {
        try {
            $arrayErrors = [];
            $countErrors = 0;
            $validator = Validator::make($request->all(), [
                'file_code' => 'required|exists:reservations,file_code',
                'service' => 'required',
                'date' => 'required|date_format:d/m/Y',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {

                    array_push($arrayErrors, $error);
                }
                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'message' => $errors], 422);
            } else {
                $service_id = $request->input('service');
                $file_code = $request->input('file_code');
                $date = \DateTime::createFromFormat('d/m/Y', $request->input('date'));

                $service_exist = Reservation::with([
                    'reservationsService' => function ($query) use ($service_id, $date) {
                        $query->where('service_id', $service_id);
                        $query->where('date', $date->format('Y-m-d'));
                        $query->where('status', 1);
                    },
                ])->where('file_code', $request->input('file_code'))->first();

                if ($service_exist->reservationsService->count() == 0) {
                    return Response::json(
                        [
                            'success' => false,
                            'message' => 'The service is not registered in the file',
                        ],
                        422
                    );
                }

                $service_id_check = Ticket::where('file_code', $file_code)
                    ->where('object_id', $service_id)
                    ->where('type', 'service')
                    ->where('status', 0)
                    ->get();

                if ($service_id_check->count() > 0) {
                    return Response::json([
                        'success' => false,
                        'message' => 'The cancellation of the service is already registered',
                    ], 422);
                }

                $ticket = new Ticket();
                $ticket->type = 'service';
                $ticket->file_code = $request->input('file_code');
                $ticket->object_id = $request->input('service');
                $ticket->origin = 'API';
                $ticket->action = 'cancellation';
                $ticket->status = 0;
                $ticket->date_service = $date->format('Y-m-d');
                $ticket->save();
                $this->sendNotificationTicket($ticket);
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function sendNotificationTicket($ticket)
    {
        // Obtener el ticket con las relaciones necesarias
        $ticketData = Ticket::with(['service', 'hotel'])
            ->where('id', $ticket->id)
            ->first();

        if (!$ticketData) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
        }

        // Transformar el ticket con la información del cliente y la reserva
        $reservation = Reservation::where('file_code', $ticketData->file_code)->with('create_user')->first();
        $client = $reservation
            ? Client::where('id', $reservation->client_id)
                ->with('client_executives:name,email')
                ->first()
            : null;

        $ticketData->reservation = $reservation->toArray();
        $ticketData->client = $client->toArray();

        $ticketData = $this->utf8ize($ticketData); // Asegurar codificación UTF-8

        // Verificar si el cliente tiene ejecutivos asociados
        if ($client && $client->client_executives->isNotEmpty()) {
            // Obtener los correos electrónicos de los ejecutivos
            $emails = $client->client_executives->map(function ($executive) {
                return [
                    'name' => $executive->name,
                    'email' => $executive->email,
                ];
            })->toArray();

            // Enviar correo dependiendo del entorno
            if (App::environment('local')) {
                $testEmails = [
                    ['name' => 'Jean Pierre Garay', 'email' => 'jgq@limatours.com.pe'],
                ];
                Mail::to($testEmails)->send(new NotificationTicket($ticketData->toArray()));
            } else {
                Mail::to($emails)->send(new NotificationTicket($ticketData->toArray()));
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No executives found for this client'], 404);
        }
    }

    private function utf8ize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'utf8ize'], $data);
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = $this->utf8ize($value);
            }
            return $data;
        } else {
            return is_string($data) ? mb_convert_encoding($data, 'UTF-8', 'UTF-8') : $data;
        }
    }

    public function destinations_masi(Request $request)
    {
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        try {
            if ($request->get('services_id')) {
                $service_client = $this->getClientServices(
                    $client_id,
                    Carbon::now('America/Lima')->year,
                    true,
                    $request->get('services_id')
                );
            } else {
                $service_client = $this->getClientServices($client_id, Carbon::now('America/Lima')->year, true);
            }

            // obtengo los destinos
            $services_destinations = $this->getDestinationsServices($service_client, ServiceDestination::class);

            $destination_select = [];
            foreach ($services_destinations as $services_destination) {
                if ($services_destination["city_id"] != null) {
                    $check_exists_city = false;
                    foreach ($destination_select as $destiny) {
                        if ($destiny["label"] == $services_destination["city"]["translations"][0]["value"]) {
                            $check_exists_city = true;
                        }
                    }
                    if (!$check_exists_city) {
                        array_push($destination_select, [
                            "code" => $services_destination["country_id"] . "," . $services_destination["state_id"] . "," . $services_destination["city_id"],
                            "label" => $services_destination["city"]["translations"][0]["value"],
                        ]);
                    }
                }
            }

            return \response()->json($destination_select);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }

    public function experiences_masi(Request $request)
    {
        $language_id = $request->input('language_id');
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        try {
            if ($request->get('services_id')) {
                $service_client_ids = $this->getClientServices(
                    $client_id,
                    Carbon::now('America/Lima')->year,
                    true,
                    $request->get('services_id')
                );
            } else {
                $service_client_ids = $this->getClientServices($client_id, Carbon::now('America/Lima')->year, true);
            }

            $experience_services = ExperienceService::whereIn('service_id', $service_client_ids)->with([
                'experience.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])->distinct()->get();

            $experiences_select = [];

            foreach ($experience_services as $experience_service) {
                $check_exists_experience = false;
                foreach ($experiences_select as $experience) {
                    if ($experience["name"] == $experience_service["experience"]["translations"][0]["value"]) {
                        $check_exists_experience = true;
                    }
                }
                if (!$check_exists_experience) {
                    array_push($experiences_select, [
                        "experience_id" => $experience_service["experience_id"],
                        "name" => $experience_service["experience"]["translations"][0]["value"],
                        "selected" => false,
                    ]);
                }
            }

            return \response()->json($experiences_select);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }

    public function destinationsCountByState(Request $request)
    {
        try {
            $client_id = $this->getClientId($request);
            if ($request->get('services_id')) {
                $service_client = $this->getClientServices(
                    $client_id,
                    Carbon::now('America/Lima')->year,
                    true,
                    $request->get('services_id')
                );
            } else {
                $service_client = $this->getClientServices($client_id, Carbon::now('America/Lima')->year, true);
            }
            // obtengo los destinos de servicios

            if ($service_client->count() > 0) {
                $services_destinations = $this->getDestinationServicesCountByState($service_client);
            } else {
                $services_destinations = [];
            }
            $market = Client::find($client_id);
            // obtengo los destinos de paquetes
            $packages = PackageRateSaleMarkup::select(['id', 'seller_type', 'seller_id', 'package_plan_rate_id'])
                ->where(function ($query) use ($client_id, $market) {
                    $query->orWhere('seller_id', $client_id);
                    $query->orWhere('seller_id', $market->market_id);
                })
                ->with([
                    'plan_rate' => function ($query) {
                        $query->select(['id', 'package_id']);
                    },
                ])->where('status', 1)->get();

            $package_client = $packages->pluck('plan_rate.package_id')->unique();
            $package_destinations = $this->getDestinationPackageCountByState($package_client);

            if (count($services_destinations) > 0) {
                $data_merge = $services_destinations->merge($package_destinations);
            } else {
                $data_merge = $package_destinations;
            }
            $data = array();
            $data_merge = $data_merge->groupBy('state');
            foreach ($data_merge as $key => $group) {
                $service_total = 0;
                $package_total = 0;
                foreach ($group as $key_item => $item) {
                    $state_id = $item['state_id'];
                    $country_id = $item['country_id'];
                    $image = $this->verifyCloudinaryImg($item['image'], 350, 350, '');
                    if (isset($item['service_total'])) {
                        $service_total = $item['service_total'];
                        break;
                    }
                }
                foreach ($group as $key_item => $item) {
                    $state_id = $item['state_id'];
                    $country_id = $item['country_id'];
                    $image = $this->verifyCloudinaryImg($item['image'], 350, 350, '');
                    if (isset($item['package_total'])) {
                        $package_total = $item['package_total'];
                        break;
                    }
                }
                $data[] = [
                    "state_id" => $state_id,
                    "country_id" => $country_id,
                    "state" => $key,
                    "image" => $image,
                    "service_total" => $service_total,
                    "package_total" => $package_total,
                ];
            }

            return Response::json(['success' => true, 'data' => $data]);
        } catch (\Exception $exception) {
            return Response::json([
                'success' => false,
                'data' => $exception->getMessage() . ' - ' . $exception->getLine(),
            ]);
        }
    }

    public function packages(PackageRequest $request)
    {
        try {

            $client_id = $this->getClientId($request);
            $this->setClient($client_id);
            $destiny = $request->input('destiny');
            if (!isset($destiny)) {
                $destiny = null;
            }

            $destiny_state_id = "";

            if ($destiny !== null) {
                $destiny_codes = explode(",", $destiny['code']);
                $destiny_state_id = isset($destiny_codes[1]) ? [$destiny_codes[1]] : [''];
            }

            $package_id = Input::has('package_id') ? $request->input('package_id') : '';
            $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
            $type_service = $request->input('type_service');
            $date_from = $request->input('date');
            $from = Carbon::parse($date_from);
            $from = $from->format('Y-m-d');
            $quantity_persons = $request->input('quantity_persons');
            $adult = (int)$quantity_persons['adults'];
            $child = (int)$quantity_persons['child'];
            $age_children = $quantity_persons['age_child'];
            $type_class = ($request->has('category')) ? $request->input('category') : 'all';
            $type_package = ($request->has('type_package')) ? (int)$request->input('type_package') : 0;
            $recommendations = ($request->has('recommendations')) ? $request->input('recommendations') : false;
            $language = Language::where('iso', $lang)->first();
            $search_parameters = [
                'destiny' => ($destiny == null) ? '' : $destiny,
                'date' => $from,
                'type_service' => $type_service,
                'category' => $type_class,
                'recommendations' => $recommendations,
                'quantity_persons' => $quantity_persons,
            ];
            $client = Client::find($client_id);

            $packages = $this->getPackagesClient(
                $client,
                $destiny_state_id,
                $type_package,
                $language,
                $type_service,
                $from,
                $type_class,
                $adult,
                $child,
                0,
                [],
                '',
                0,
                $recommendations,
                false,
                $package_id
            );
            $faker = Faker::create();
            $token_search = $faker->unique()->uuid;
            $this->storeTokenSearchHotels($token_search, $packages, $this->expiration_search_services);
            $packages = $packages->transform(function ($package) use (
                $client,
                $type_class,
                $type_package,
                $package_id,
                $date_from,
                $language,
                $adult,
                $child,
                $token_search
            ) {
                $paxTotal = ($adult + $child);
                $itinerary = [];
                $itinerary_text = strip_tags(htmlDecode(htmlspecialchars_decode(
                    $package->translations[0]->itinerary_commercial,
                    ENT_QUOTES
                )));
                $count = substr_count($itinerary_text, "#");
                if ($count > 0) {
                    $textExplode = explode("#", $itinerary_text);
                    for ($j = 0; $j < $count; $j++) {
                        $texto = substr($textExplode[$j + 1], 1);
                        $itinerary[] = [
                            'day' => $j + 1,
                            'description' => ltrim($texto),
                        ];
                    }
                } else {
                    if (!empty($itinerary_text)) {
                        $itinerary[] = [
                            'day' => 1,
                            'description' => ltrim($itinerary_text),
                        ];
                    }
                };

                // Inclusiones
                $inclusions = [
                    "include" => [],
                    "no_include" => [],
                ];
                $inclusion_data = $package->inclusions;
                foreach ($inclusion_data as $inclusion) {
                    //incluye
                    if ($inclusion['include']) {
                        $inclusions['include'][] = [
                            'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                        ];
                    }

                    //no incluye
                    if (!$inclusion['include']) {
                        $inclusions['no_include'][] = [
                            'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                        ];
                    }
                }

                $allows_child = 0;
                $children_ages = [
                    'min' => 0,
                    'max' => 0,
                ];

                if ($package['allow_child'] and count($package['children']) > 0) {
                    $allows_child = 1;
                    $children_ages = [
                        'min' => $package['children'][0]['min_age'],
                        'max' => $package['children'][0]['max_age'],
                    ];
                }

                $packages = [
                    'id' => $package['id'],
                    'date_reserve' => $date_from,
                    'country_id' => $package['country_id'],
                    'code' => $package['code'],
                    'extension' => $package['extension'],
                    'nights' => $package['nights'],
                    'map_link' => $package['map_link'],
                    'image_link' => $package['image_link'],
                    'status' => $package['status'],
                    'reference' => $package['reference'],
                    'rate_type' => $package['rate_type'],
                    'rate_dynamic' => $package['rate_dynamic'],
                    'allow_guide' => $package['allow_guide'],
                    'allow_child' => $allows_child,
                    'allow_infant' => $package['allow_infant'],
                    'limit_confirmation_hours' => $package['limit_confirmation_hours'],
                    'infant_min_age' => $package['infant_min_age'],
                    'infant_max_age' => $package['infant_max_age'],
                    'physical_intensity' => (empty($package['physical_intensity'])) ? null : $package['physical_intensity'],
                    'tag_id' => $package['tag_id'],
                    'allow_modify' => $package['allow_modify'],
                    'recommended' => $package['recommended'],
                    'destinations' => $package['destinations'],
                    'inclusions' => $inclusions,
                    'itinerary' => $itinerary,
                    'child_age_allowed' => $children_ages,
                    'price' => 0,
                    'without_discount' => 0,
                    'offer' => false,
                    'favorite' => false,
                    'offer_value' => 0,
                    'rated' => ($package->rated->count() > 0) ? $package->rated[0]->rated : 0,
                    'package_destinations' => $package->package_destinations,
                    'tag' => $package->tag,
                    'translations' => $package->translations,
                    'fixed_outputs' => $package->fixed_outputs,
                    'galleries' => $package->galleries,
                    'service_types' => $package->plan_rates_service_type,
                    'plan_rates' => $package->plan_rates,
                    'quantity_adult' => $adult,
                    'quantity_child' => $child,
                    'extension_recommended' => $package->extension_recommended,
                    'available_from' => [],
                    'token_search' => $token_search,
                ];

                $packages['available_from'] = [
                    'from' => 0,
                    'unit_duration' => '',
                ];

                if ($package->client_package_setting->count() > 0) {
                    $packages['available_from'] = [
                        'from' => $package->client_package_setting[0]->reservation_from,
                        'unit_duration' => ($package->client_package_setting[0]->unit_duration_reserve == 2) ? 'days' : 'hours',
                    ];
                }

                $package_rate_sale_markup_id = '';
                foreach ($package["plan_rates"] as $package_plan_rate) {
                    $offer_query = $package_plan_rate->offers;
                    $dynamicSaleRates = collect();
                    //Obtengo el markup por el mercado del cliente
                    if ($package_plan_rate->package_rate_sale_markup_market->count() > 0) {
                        $package_rate_sale_markup_id = $package_plan_rate->package_rate_sale_markup_market[0]["id"];
                        $dynamicSaleRates = $package_plan_rate->package_rate_sale_markup_market[0]->dynamic_sale_rates;
                    }

                    //Obtengo el markup por el cliente
                    if ($package_plan_rate->package_rate_sale_markup->count() > 0) {
                        $package_rate_sale_markup_id = $package_plan_rate->package_rate_sale_markup[0]["id"];
                        $dynamicSaleRates = $package_plan_rate->package_rate_sale_markup[0]->dynamic_sale_rates;
                    }

                    if ($package_rate_sale_markup_id != '') {
                        $fields = $this->getFieldPriceByTotalPax($paxTotal, $package_plan_rate->service_type_id);
                        $field = $fields['field'];
                        $rangePax = $fields['range_pax'];

                        $min_price = $this->getMinPriceDynamicSaleRates(
                            $dynamicSaleRates,
                            $package_plan_rate['plan_rate_categories'][0]['id'],
                            $package_plan_rate->service_type_id,
                            $field,
                            $rangePax
                        );
                        if ($offer_query->count() > 0) {
                            $is_offer = (bool)$offer_query[0]->is_offer;
                            $offer_value = $offer_query[0]->value;
                            if ($is_offer) {
                                $packages["offer"] = true;
                                $without_discount = $min_price * $paxTotal;
                                $packages["without_discount"] = (float)roundLito($without_discount);
                                $min_price = $min_price - ($min_price * ($offer_value / 100));
                                $packages["offer_value"] = $offer_value;
                            } else {
                                $min_price = $min_price + ($min_price * ($offer_value / 100));
                            }
                        } else {
                            $packages["offer"] = false;
                        }
                        $total_amount = $min_price * $paxTotal;
                        $packages["price"] = (float)roundLito($total_amount);
                    }
                }
                return $packages;
            });

            $packages = PackageResource::collection($packages);

            if ($recommendations) {
                $packages = $packages->sortByDesc('rated')->values();
            }

            $data = [
                "token_search" => $token_search,
                'search_parameters' => $search_parameters,
                'packages' => $packages,
                'quantity_packages' => $packages->count(),
            ];

            return Response::json(['success' => true, 'data' => $data]);
        } catch (\Exception $exception) {
            return Response::json([
                'success' => false,
                'data' => $exception->getMessage() . ' - ' . $exception->getLine(),
            ]);
        }
    }

    public function services_details(ServiceDetailRequest $request)
    {
        try {
            $symbol_to_change = '';
            $iso_to_change = '';
            $services = [];
            $lang = $request->input('lang');
            $services_data = $this->getInformationServices($request->input('id'), $request->input('lang'));
            $services_data = $services_data->toArray();
            foreach ($services_data as $index_service => $service_client) {
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
                        'max' => 0,
                    ];
                    if ($service_client['allow_child'] and count($service_client['children_ages']) > 0) {
                        $allows_child = 1;
                        $children_ages = [
                            'min' => $service_client['children_ages'][0]['min_age'],
                            'max' => $service_client['children_ages'][0]['max_age'],
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
                    $symbol = $service_client['currency']['symbol'];
                    $iso = $service_client['currency']['symbol'];
                    if ($service_client['currency']['iso'] == 'PEN' & $symbol_to_change !== '') {
                        //Moneda
                        $symbol = $symbol_to_change;
                        $iso = $iso_to_change;
                    }
                    //Modeda
                    $services[$index_service]['currency'] = [
                        'symbol' => $symbol,
                        'iso' => $iso,
                    ];

                    //Categoria
                    $services[$index_service]['category'] = [
                        'category' => $service_client['service_sub_category']['service_categories']['translations'][0]['value'],
                        'sub_category' => $service_client['service_sub_category']['translations'][0]['value'],
                    ];
                    //Duracion del servicio
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

                    //tipo de servicio
                    $services[$index_service]['service_type'] = [
                        'id' => $service_client['service_type']['id'],
                        'name' => $service_client['service_type']['translations'][0]['value'],
                        'code' => $service_client['service_type']['code'],
                    ];

                    //clasificacion
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
                        'image' => $image_classification,
                    ];

                    $itinerary_commercial = [];
                    if (!empty($service_client['service_translations'][0]['itinerary_commercial'])) {
                        $itinerary_commercial = $this->getFormatItinerary($service_client['service_translations'][0]['itinerary_commercial']);
                    }
                    //Descripcion
                    $services[$index_service]['descriptions'] = [
                        'name_commercial' => $service_client['service_translations'][0]['name_commercial'],
                        'description' => $service_client['service_translations'][0]['description_commercial'],
                        'itinerary' => $itinerary_commercial,
                        'summary' => $service_client['service_translations'][0]['summary_commercial'],
                    ];

                    $services[$index_service]['languages_guide'] = [
                        'language_display' => '',
                        'iso_display' => '',
                        'languages' => [],
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
                            'languages' => $languages,
                        ];
                    }

                    $services[$index_service]['experiences'] = [];
                    //Experiencias
                    foreach ($service_client['experience'] as $experience) {
                        $services[$index_service]['experiences'][] = [
                            'id' => $experience['id'],
                            'name' => $experience['translations'][0]['value'],
                            'color' => $experience['color'],
                        ];
                    }

                    $services[$index_service]['restrictions'] = [];
                    //restricciones
                    foreach ($service_client['restriction'] as $restriction) {
                        $services[$index_service]['restrictions'][] = [
                            'id' => $restriction['id'],
                            'name' => $restriction['translations'][0]['value'],
                        ];
                    }

                    $services[$index_service]['galleries'] = [];
                    //Galerias
                    foreach ($service_client['galleries'] as $gallery) {
                        $services[$index_service]['galleries'][] = [
                            'url' => $this->verifyCloudinaryImg($gallery['url'], 400, 450, ''),
                        ];
                    }

                    $services[$index_service]['operations']['turns'] = [];
                    //Detalle dia a dia de las operaciones

                    $key_ = 0;

                    foreach ($service_client['schedules'] as $keyA_ => $itemA_) {

                        $operabilities = ServiceOperation::where('service_id', $services[$index_service]['id'])
                            ->where('service_schedule_id', $itemA_['id'])
                            ->with([
                                'services_operation_activities.service_type_activities.translations' => function (
                                    $query
                                ) use (
                                    $lang
                                ) {
                                    $query->where('type', 'servicetypeactivity');
                                    $query->whereHas('language', function ($q) use ($lang) {
                                        $q->where('iso', $lang);
                                    });
                                },
                            ])->get()->toArray();

                        $key = 0;
                        foreach ($operabilities as $keyA => $itemA) {
                            $start_time = $itemA['start_time'];
                            $services[$index_service]['operations']['turns'][$key_][$key] = [
                                'day' => $itemA['day'],
                                'departure_time' => $itemA['start_time'],
                                'shifts_available' => $itemA['shifts_available'],
                                'detail' => [],
                            ];
                            foreach ($itemA['services_operation_activities'] as $keyB => $itemB) {
                                if ($keyB > 0) {
                                    $count = count($services[$index_service]['operations']['turns'][$key_][$key]['detail']);
                                    $start_time = $services[$index_service]['operations']['turns'][$key_][$key]['detail'][$count - 1]['end_time'];
                                }
                                $start_end = Carbon::createFromFormat(
                                    'H:i:s',
                                    $start_time
                                )->addMinutes($itemB['minutes'])->toTimeString();

                                $services[$index_service]['operations']['turns'][$key_][$key]['detail'][] = [
                                    'detail' => (count($itemB['service_type_activities']['translations']) == 0) ? '' : $itemB['service_type_activities']['translations'][0]['value'],
                                    'start_time' => $start_time,
                                    'end_time' => $start_end,
                                ];
                            }
                            $key++;
                        }
                        $key_++;
                    }

                    $services[$index_service]['operations']['schedule'] = [];
                    //Horario de opearcion
                    $schedules = $service_client['schedules'];

                    foreach ($schedules as $schedule) {
                        $monday = $schedule['services_schedule_detail'][0]['monday'] . ' - ' . $schedule['services_schedule_detail'][1]['monday'];
                        $tuesday = $schedule['services_schedule_detail'][0]['tuesday'] . ' - ' . $schedule['services_schedule_detail'][1]['tuesday'];
                        $wednesday = $schedule['services_schedule_detail'][0]['wednesday'] . ' - ' . $schedule['services_schedule_detail'][1]['wednesday'];
                        $thursday = $schedule['services_schedule_detail'][0]['thursday'] . ' - ' . $schedule['services_schedule_detail'][1]['thursday'];
                        $friday = $schedule['services_schedule_detail'][0]['friday'] . ' - ' . $schedule['services_schedule_detail'][1]['friday'];
                        $saturday = $schedule['services_schedule_detail'][0]['saturday'] . ' - ' . $schedule['services_schedule_detail'][1]['saturday'];
                        $sunday = $schedule['services_schedule_detail'][0]['sunday'] . ' - ' . $schedule['services_schedule_detail'][1]['sunday'];
                        $services[$index_service]['operations']['schedule'][] = [
                            'monday' => $monday,
                            'tuesday' => $tuesday,
                            'wednesday' => $wednesday,
                            'thursday' => $thursday,
                            'friday' => $friday,
                            'saturday' => $saturday,
                            'sunday' => $sunday,
                        ];
                    }

                    // Inclusiones
                    $inclusions = $service_client['inclusions'];
                    foreach ($inclusions as $inclusion) {
                        //incluye
                        if ($inclusion['include']) {
                            $services[$index_service]['inclusions'][$inclusion['day']]['include'][] = [
                                'day' => $inclusion['day'],
                                'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                            ];
                        }

                        //no incluye
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
                } catch (\Exception $e) {
                    return Response::json([
                        'data' => [],
                        'success' => false,
                        'message' => $service_client['aurora_code'] . ' - ' . $e->getLine(),
                        'expiration_token' => '',
                    ]);
                }
            }
            return Response::json(['success' => true, 'data' => $services]);
        } catch (\Exception $exception) {
            return Response::json([
                'success' => false,
                'data' => $exception->getMessage() . ' - ' . $exception->getLine(),
            ]);
        }
    }

    public function destinations_packages(Request $request)
    {
        $client_id = $this->getClientId($request);

        try {
            $market = Client::find($client_id);
            // obtengo los destinos de paquetes
            $packages = PackageRateSaleMarkup::select(['id', 'seller_type', 'seller_id', 'package_plan_rate_id'])
                ->where(function ($query) use ($client_id, $market) {
                    $query->orWhere('seller_id', $client_id);
                    $query->orWhere('seller_id', $market->market_id);
                })
                ->with([
                    'plan_rate' => function ($query) {
                        $query->select(['id', 'package_id']);
                    },
                ])->where('status', 1)->get();

            $package_client = $packages->pluck('plan_rate.package_id')->unique();
            $package_destinations = $this->getDestinationPackageState($package_client);
            return Response::json(['success' => true, 'data' => $package_destinations]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }

    public function packages_details(ServiceDetailRequest $request)
    {
        try {
            $packages_data = $this->getInformationPackages($request->input('id'), $request->input('lang'));
            $packages = $packages_data->transform(function ($package) {
                return [
                    'id' => $package['id'],
                    'country_id' => $package['country_id'],
                    'code' => $package['code'],
                    'extension' => $package['extension'],
                    'nights' => $package['nights'],
                    'map_link' => $package['map_link'],
                    'image_link' => $package['image_link'],
                    'status' => $package['status'],
                    'physical_intensity' => (empty($package['physical_intensity'])) ? null : $package['physical_intensity'],
                    'tag_id' => $package['tag_id'],
                    'destinations' => $package['destinations'],
                    'package_destinations' => $package->package_destinations,
                    'tag' => $package->tag,
                    'translations' => $package->translations,
                    'galleries' => $package->galleries,
                ];
            });

            $packages = PackageDetailsResource::collection($packages);

            return Response::json(['success' => true, 'data' => $packages]);
        } catch (\Exception $exception) {
            return Response::json([
                'success' => false,
                'data' => $exception->getMessage() . ' - ' . $exception->getLine(),
            ]);
        }
    }

    public function addSupplementToService(Request $request)
    {
        $adults = $request->get('adults');
        $child = $request->get('child');
        $dates = $request->get('dates');
        $service_id = $request->get('service_id');
        $supplement_id = $request->get('supplement_id');
        $token_search = $request->get('token_search');
        $supplements = $this->changeInputsSupplement(
            $adults,
            $child,
            $dates,
            $service_id,
            $supplement_id,
            $token_search
        );

        if ($supplements['total_amount'] === 0) {
            return Response::json(['success' => false, 'error' => 'No se encontraron tarifas', 'data' => []]);
        }

        return Response::json(['success' => true, 'data' => $supplements]);
    }

    public function hotel_list_packages(Request $request)
    {

        $lang = $request->input('lang');
        $client_id = $request->input('client_id');
        $quantity_persons = $request->input('quantity_persons');
        $room_occupation = $request->input('room_occupation');
        $dates_ranges = $request->input('dates_ranges');
        $category_id = $request->input('category_id');
        $year_today = Carbon::now()->year;

        $hotel_client_ids = HotelClient::select('hotel_id')->where('client_id', $client_id)->where(
            'period',
            $year_today
        )->get()->toArray();

        $client_rate_plan_ids = ClientRatePlan::select('rate_plan_id')->where('client_id', $client_id)->where(
            'period',
            $year_today
        )->get()->toArray();
        $date_range_hotels = [];

        foreach ($dates_ranges as $dates_range) {

            $quantity_nights = Carbon::parse($dates_range["date_from"])->diffInDays(Carbon::parse($dates_range["date_to"]));

            $new_date_to = Carbon::parse($dates_range["date_to"])->subDay()->format('Y-m-d');

            $list_hotels_on_request = [];
            $list_hotels_available = [];

            $hotels = Hotel::where('typeclass_id', $category_id)->where('status', 1)
                ->where('state_id', $dates_range["state_id"])
                ->where('city_id', $dates_range["city_id"])
                ->whereNotIn('id', $hotel_client_ids)
                ->get();

            foreach ($hotels as $hotel) {

                $rate_plans = RatesPlans::where('status', 1)->where(
                    'hotel_id',
                    $hotel["id"]
                )->where('rates_plans_type_id', 2)->whereNotIn('id', $client_rate_plan_ids)->get();

                $rooms = [];

                foreach ($rate_plans as $rate_plan) {
                    $dates_range_hotel = DateRangeHotel::with([
                        'room.room_type',
                        'room.translations',
                    ])->where('rate_plan_id', $rate_plan['id'])
                        ->where('date_from', '<=', $dates_range["date_from"])
                        ->where('date_to', '>=', $dates_range["date_to"])
                        ->orderBy('price_adult')
                        ->get();

                    foreach ($dates_range_hotel as $date_range_hotel) {
                        $on_request_room = 1; //RQ
                        $quantity_rooms = 0;
                        if ($date_range_hotel["room"]["room_type"]["occupation"] == 1) {

                            $quantity_rooms = $room_occupation["sgl"];
                        }
                        if ($date_range_hotel["room"]["room_type"]["occupation"] == 2) {

                            $quantity_rooms = $room_occupation["dbl"];
                        }
                        if ($date_range_hotel["room"]["room_type"]["occupation"] == 3) {

                            $quantity_rooms = $room_occupation["tpl"];
                        }
                        $rate_plan_room = RatesPlansRooms::find($date_range_hotel["rate_plan_room_id"]);
                        if ($rate_plan_room != null) {
                            if ($rate_plan_room->bag == 1) {

                                $bag_rate = BagRate::select('bag_room_id')->where(
                                    'rate_plan_rooms_id',
                                    $rate_plan_room->id
                                )->first();

                                if ($bag_rate != null) {

                                    $inventories = InventoryBag::where('date', '>=', $dates_range["date_from"])
                                        ->where('date', '<=', $new_date_to)
                                        ->where('locked', 0)
                                        ->where('inventory_num', '>=', $quantity_rooms)
                                        ->where('bag_room_id', $bag_rate->bag_room_id)->get();

                                    if ($inventories->count() == $quantity_nights) {
                                        $on_request_room = 0;
                                    }
                                }
                            }
                            if ($rate_plan_room->bag == 0) {
                                $inventories = Inventory::where('date', '>=', $dates_range["date_from"])
                                    ->where('date', '<=', $new_date_to)
                                    ->where('locked', 0)
                                    ->where('inventory_num', '>=', $quantity_rooms)
                                    ->where('rate_plan_rooms_id', $rate_plan_room->id)->get();

                                if ($inventories->count() == $quantity_nights) {
                                    $on_request_room = 0; // OK
                                }
                            }
                        }
                        array_push($rooms, [
                            "room_id" => $date_range_hotel["room_id"],
                            "rate_plan_id" => $date_range_hotel["rate_plan_id"],
                            "rate_plan_room_id" => $date_range_hotel["rate_plan_room_id"],
                            "price_adult" => $date_range_hotel["price_adult"],
                            "price_child" => $date_range_hotel["price_child"],
                            "occupation" => $date_range_hotel["room"]["room_type"]["occupation"],
                            "quantity_rooms" => $quantity_rooms,
                            "on_request" => $on_request_room,
                        ]);
                    }
                }
                if (count($rooms) > 0) {
                    $on_request_hotel = null;

                    if ($room_occupation["sgl"] > 0) {
                        foreach ($rooms as $room) {
                            if ($room["occupation"] == 1) {
                                if ($room["on_request"] == 0) {
                                    $on_request_hotel = 0;
                                    break;
                                }
                            }
                            $on_request_hotel = 1;
                        }
                    }
                    if ($on_request_hotel == null || $on_request_hotel == 0) {
                        if ($room_occupation["dbl"] > 0) {
                            foreach ($rooms as $room) {
                                if ($room["occupation"] == 2) {
                                    if ($room["on_request"] == 0) {
                                        $on_request_hotel = 0;
                                        break;
                                    }
                                }
                                $on_request_hotel = 1;
                            }
                        }
                    }

                    if ($on_request_hotel == null || $on_request_hotel == 0) {
                        if ($room_occupation["tpl"] > 0) {
                            foreach ($rooms as $room) {
                                if ($room["occupation"] == 3) {
                                    if ($room["on_request"] == 0) {
                                        $on_request_hotel = 0;
                                        break;
                                    }
                                }
                                $on_request_hotel = 1;
                            }
                        }
                    }

                    if ($on_request_hotel == 0) {
                        array_push($list_hotels_available, [
                            "hotel_id" => $hotel["id"],
                            "hotel_name" => $hotel["name"],
                            "rooms" => $rooms,
                            "on_request" => $on_request_hotel,
                        ]);
                    }
                    if ($on_request_hotel == 1) {
                        array_push($list_hotels_on_request, [
                            "hotel_id" => $hotel["id"],
                            "hotel_name" => $hotel["name"],
                            "rooms" => $rooms,
                            "on_request" => $on_request_hotel,
                        ]);
                    }
                }
            }
            array_push($date_range_hotels, [
                "date_from" => $dates_range["date_from"],
                "date_to" => $dates_range["date_to"],
                "hotels_on_request" => $list_hotels_on_request,
                "hotels" => $list_hotels_available,
            ]);
        }
        return \response()->json($date_range_hotels);
    }

    public function reservationPackageClient(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'en';
        $client_id = $request->input('client_id');
        $passengers = ($request->has('passengers')) ? $request->input('passengers') : [];
        $type_class_id = $request->input('type_class_id');
        $service_type_id = $request->input('service_type_id');
        $hotels_changed = ($request->has('hotels_changed')) ? $request->input('hotels_changed') : [];
        $date = $request->input('date');
        $token_search = $request->input('token_search');
        $rooms = $request->input('rooms');
        $quantity_persons = $request->input('quantity_persons');
        $quantity_rooms_sgl = (int)$rooms['quantity_sgl'];
        $quantity_rooms_dbl = (int)$rooms['quantity_dbl'];
        $quantity_rooms_child_dbl = (int)$rooms['quantity_child_dbl'];
        $quantity_rooms_tpl = (int)$rooms['quantity_tpl'];
        $quantity_rooms_child_tpl = (int)$rooms['quantity_child_tpl'];
        $reference_new = $request->input('reference');

        $services_added_package = ($request->has('services_added_package')) ? $request->input('services_added_package') : [];

        $file_code = $request->input('file_code');

        $packages_selected = Cache::get($token_search);

        if (count($packages_selected) === 0) {
            return Response::json([
                'success' => false,
                'message' => 'Su tiempo de reserva termino, por favor vuelva a buscar su paquete',
            ], 404);
        }

        $packages_selected = $packages_selected[0];
        $package_name = $packages_selected['translations'][0]['tradename'];
        //Todo: Cantidad real de pax
        $quantity_adults = $quantity_persons["adults"];
        $quantity_child = ($quantity_persons["child_with_bed"] + $quantity_persons["child_without_bed"]);

        //Todo: Cantidad de habitaciones
        $only_on_request = false;
        $total_rooms = $quantity_rooms_sgl + $quantity_rooms_dbl + $quantity_rooms_tpl;
        //Todo: Si la cantidad total de habitaciones es >= 4 la reserva baja en on request
        if ($total_rooms >= 4) {
            $only_on_request = true;
        }

        if (isset($passengers) and is_array($passengers) and count($passengers) > 0) {
            $pax_first = current($passengers);
            $reference = $pax_first["nombres"] . ' ' . $pax_first["apellidos"];
        } else {
            $reference = $package_name;
        }

        $reservation_data = [
            "client_id" => $client_id,
            "file_code" => $file_code,
            "reference" => $reference,
            "reference_new" => $reference_new,
            "total_amount" => $packages_selected['total_amount'],
            "subtotal_amount" => $packages_selected['total_amount'],
            "guests" => [
                [
                    "given_name" => "",
                    "surname" => "",
                ],
            ],
            "reservations" => [],
            "reservations_services" => [],
            "reservations_flights" => [],
            "date_init" => $date,
            "entity" => 'Package',
            "entity_data" => [
                "name" => $package_name,
                "adults" => $packages_selected['quantity_adult'],
                "quantity_sgl" => $quantity_rooms_sgl,
                "quantity_dbl" => $quantity_rooms_dbl,
                "quantity_child_dbl" => $quantity_rooms_child_dbl,
                "quantity_tpl" => $quantity_rooms_tpl,
                "quantity_child_tpl" => $quantity_rooms_child_tpl,
                "type_class_id" => $type_class_id,
                "service_type_id" => $service_type_id,
                "children_with_bed" => $packages_selected['quantity_child']['with_bed'],
                "children_without_bed" => $packages_selected['quantity_child']['without_bed'],
                "price_per_adult_sgl" => $packages_selected['price_per_adult']['room_sgl'],
                "price_per_adult_dbl" => $packages_selected['price_per_adult']['room_dbl'],
                "price_per_adult_tpl" => $packages_selected['price_per_adult']['room_tpl'],
                "price_per_child_with_bed" => $packages_selected['price_per_child']['with_bed'],
                "price_per_child_without_bed" => $packages_selected['price_per_child']['without_bed'],
                "cancellation_policy" => $packages_selected['cancellation_policy'],
            ],
            "object_id" => $packages_selected['id'],
        ];

        if (Auth::user()->user_type_id == 3) {
            $reservation_data['executive_id'] = Auth::user()->id;
        }

        $reservation_passengers = [];
        $ages_child = [];

        if (!empty($passengers) and count($passengers) > 0) {
            foreach ($passengers as $passenger) {

                if ($passenger["tipo"] === 'CHD' and empty($passenger["fecnac"])) {
                    throw new \Exception('The date of birth in children is mandatory', 403);
                }

                if (!empty($passenger["fecnac"])) {
                    $date_birth = Carbon::createFromFormat('d/m/Y', $passenger["fecnac"])->format('Y-m-d');
                } else {
                    $date_birth = null;
                }

                if ($passenger["tipo"] === 'CHD') {
                    $age_child = Carbon::parse($date_birth)->age;
                    if ($age_child >= 1) {
                        array_push($ages_child, ['age' => $age_child]);
                    } else {
                        throw new \Exception('The age of the children must be greater than or equal to 1 year', 403);
                    }
                }

                array_push($reservation_passengers, [
                    'doctype_iso' => (isset($passenger["tipdoc"])) ? $passenger["tipdoc"] : null,
                    'document_number' => $passenger["nrodoc"],
                    'given_name' => $passenger["nombres"],
                    'surname' => $passenger["apellidos"],
                    'date_birth' => $date_birth,
                    'genre' => $passenger["sexo"],
                    'type' => $passenger["tipo"],
                    'phone' => $passenger["celula"],
                    'dietary_restrictions' => $passenger["resali"],
                    'medical_restrictions' => $passenger["resmed"],
                    'notes' => $passenger["observ"],
                    'country_iso' => $passenger["nacion"],
                    'email' => $passenger["correo"],
                ]);
            }
            $reservation_data["guests"] = $reservation_passengers;
        }

        //Todo: Hacemos el cambio de hoteles en caso lo tenga
        if (count($hotels_changed) > 0) {
            foreach ($packages_selected["services"] as $key => $service) {
                if ($service["type"] == "hotel") {
                    foreach ($hotels_changed as $hotel) {
                        if ($service['id'] === $hotel['package_service_id']) {
                            $packages_selected["services"][$key]['object_id'] = $hotel['hotel_id'];
                            $service_rooms = [];
                            foreach ($hotel['rooms'] as $room) {

                                // VERIFICAR SI LA ROOM ES HYPERGUEST
                                $isHyperguest = ChannelRoom::where('room_id', $room['room_id'])->where('channel_id', 6)->where('type', 2)->where('state', 1)->exists();

                                $service_rooms[] = [
                                    'rate_plan_room_id' => $room['rate_plan_room_id'],
                                    'rate_plan_id' => $room['rate_plan_id'],
                                    'package_service_id' => $service['id'],
                                    'rate_provider' => $room['rate_provider'] ?? "",
                                    'room_id' => $room['room_id'],
                                    'hyperguest_pull' => $isHyperguest
                                ];

                            }
                            $packages_selected["services"][$key]['service_rooms'] = $service_rooms;
                        }
                    }
                }
            }
        }
        $error_services = [];

        $validation_errors = collect([
            'hotels' => collect(),
            'services' => collect(),
        ]);

        $validateHotel = [];
        $hotelesNoReservadoPaquete = [];
        foreach ($packages_selected["services"] as $service) {
            try {
                //Todo Hoteles
                if ($service["type"] == "hotel") {
                    /*
                     * Todo Variable para verificar si encontro el tipo de habitacion
                     */
                    $verify_available_room = [
                        'simple' => false,
                        'double' => false,
                        'triple' => false,
                    ];

                    foreach ($service["service_rooms"] as $index => $service_room) {
                        try {
                            if($service_room['hyperguest_pull']){
                                $room_model = Room::find($service_room['room_id']);
                                $room_type = RoomType::find($room_model->room_type_id);
                                $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                            }else{
                                $rate_plan_room_model = RatesPlansRooms::find($service_room["rate_plan_room_id"]);
                                $room_model = Room::find($rate_plan_room_model->room_id);
                                $room_type = RoomType::find($room_model->room_type_id);
                                $service["service_rooms"][$index]["occupation"] = $room_type->occupation;
                            }

                        } catch (\Exception $e) {
                            $hotel_validate = Hotel::where('id', $service['object_id'])
                                ->withTrashed()
                                ->with([
                                    'channels' => function ($query) {
                                        $query->where('state', 1);
                                    },
                                ])
                                ->first(['id']);
                            return Response::json([
                                'success' => false,
                                'errors' => [
                                    'hotels' => [
                                        [
                                            'hotel_code' => $hotel_validate->channels[0]->pivot->code,
                                            'errors' => [
                                                [
                                                    'error' => trans(
                                                        'validations.quotes.hotels.hotel_rate_deleted',
                                                        ['room_id' => $service_room["rate_plan_room_id"]]
                                                    ),
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]);
                        }
                    }
                    //return response()->json($service["service_rooms"][0]["rate_plan_room_id"]);
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

                        // $hotelsRequest = Request::createFrom($request);

                        $paramsRequest = [
                            'client_id' => $client_id,
                            'hotels_id' => $hotels_id,
                            'destiny' => [
                                'code' => $destinations->getData()[0]->code,
                                'label' => $destinations->getData()[0]->label,
                            ],
                            'hotels_search_code' => '',
                            'lang' => $lang,
                            'date_from' => $check_in,
                            'date_to' => $check_out,
                            'typeclass_id' => 'all',
                            'quantity_rooms' => 1,
                            'quantity_persons_rooms' => [],
                            'zero_rates' => true,
                        ];

                        // $isHyperguestPull = $package_service->hyperguest_pull === 1 ? true : false;
                        $hotelSearchRequest = $this->convertToHotelSearchRequest($paramsRequest);

                        $serviceControllerAvailable = new ServiceControllerAvailable();
                        $hotel_search = $serviceControllerAvailable->hotels_channels($hotelSearchRequest);

                        $result_hotel = $hotel_search->getData()->data[0]->city;
                        $token_search = $result_hotel->token_search;

                        if (count($result_hotel->hotels) === 0) {
                            $validation_errors['hotels']->add($this->validationsQuoteHotels(
                                $service,
                                $client_id,
                                $check_in,
                                $service["service_rooms"]
                            ));
                            continue;
                        }

                        $seReservoHotel = false;
                        foreach ($result_hotel->hotels as $hotel_ind => $hotel) {
                            if ($hotel->id != $hotel_id) {
                                continue;
                            }

                            $room_request = Request::createFrom($request); //$request->toArray();

                            $rooms_selected = [];
                            //Todo Reserva para Simple
                            foreach ($service["service_rooms"] as $service_room) {
                                if ($service_room["occupation"] == 1) {
                                    if ($quantity_rooms_sgl > 0) {
                                        foreach ($hotel->rooms as $room_ind => $room) {
                                            //return response()->json($room,200);
                                            foreach ($room->rates as $rate) {
                                                if ($service_room['hyperguest_pull'] == 1) {
                                                    if ($rate->ratePlanId == $service_room['rate_plan_id'] && $room->room_id == $service_room['room_id']) {
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
                                                                    // 'quantity_adults' => 1,
                                                                    'quantity_adults' => 2,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                            // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                        ]);

                                                        for ($i = 0; $i < $quantity_rooms_dbl; $i++) {

                                                            $node_reservations = [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                // "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "only_on_request" => $only_on_request,
                                                                // "quantity_adults" => $quantity_adults,
                                                                "quantity_adults" => 2,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => [],
                                                                // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                            ];
                                                            array_push(
                                                                $reservation_data["reservations"],
                                                                $node_reservations
                                                            );
                                                            $validateHotel[$result_hotel->token_search][] = $node_reservations;
                                                            $seReservoHotel = true;
                                                        }
                                                        break;
                                                    }
                                                }else {
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
                                                            $node_reservations = [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "only_on_request" => $only_on_request,
                                                                // "quantity_adults" => $quantity_adults,
                                                                "quantity_adults" => 1,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => [],
                                                            ];
                                                            array_push(
                                                                $reservation_data["reservations"],
                                                                $node_reservations
                                                            );
                                                            $validateHotel[$result_hotel->token_search][] = $node_reservations;
                                                            $seReservoHotel = true;
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            //Todo Reserva para Doble
                            foreach ($service["service_rooms"] as $service_room) {
                                if ($service_room["occupation"] == 2) {
                                    if ($quantity_rooms_dbl > 0) {
                                        foreach ($hotel->rooms as $room_ind => $room) {
                                            //return response()->json($room,200);
                                            foreach ($room->rates as $rate) {
                                                if($service_room['hyperguest_pull']) {
                                                    if ($rate->ratePlanId == $service_room['rate_plan_id'] && $room->room_id == $service_room['room_id']) {
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
                                                                    // 'quantity_adults' => 1,
                                                                    'quantity_adults' => 2,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                            // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                            // 'rate_plan' => $service_room["rate_plan"],
                                                        ]);

                                                        for ($i = 0; $i < $quantity_rooms_dbl; $i++) {

                                                            $node_reservations = [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                // "rate_plan_room_id" => $rate->ratePlanId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "only_on_request" => $only_on_request,
                                                                // "quantity_adults" => $quantity_adults,
                                                                "quantity_adults" => 2,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => [],
                                                                // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                                // 'rate_plan' => $service_room["rate_plan"],
                                                            ];
                                                            array_push(
                                                                $reservation_data["reservations"],
                                                                $node_reservations
                                                            );
                                                            $validateHotel[$result_hotel->token_search][] = $node_reservations;
                                                            $seReservoHotel = true;
                                                        }
                                                        break;
                                                    }
                                                }else {
                                                    if ($rate->rateId == $service_room["rate_plan_room_id"]) {
                                                        // return response()->json($hotel->rooms,200);
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
                                                                    // 'quantity_adults' => 1,
                                                                    'quantity_adults' => 2,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                            // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                            // 'rate_plan' => $service_room["rate_plan"],
                                                        ]);

                                                        for ($i = 0; $i < $quantity_rooms_dbl; $i++) {

                                                            $node_reservations = [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                // "rate_plan_room_id" => $rate->ratePlanId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "only_on_request" => $only_on_request,
                                                                // "quantity_adults" => $quantity_adults,
                                                                "quantity_adults" => 2,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => [],
                                                                // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                                // 'rate_plan' => $service_room["rate_plan"],
                                                            ];
                                                            array_push(
                                                                $reservation_data["reservations"],
                                                                $node_reservations
                                                            );
                                                            $validateHotel[$result_hotel->token_search][] = $node_reservations;
                                                            $seReservoHotel = true;
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            //Todo Reserva para Triple
                            foreach ($service["service_rooms"] as $service_room) {
                                if ($service_room["occupation"] == 3) {
                                    if ($quantity_rooms_tpl > 0) {
                                        foreach ($hotel->rooms as $room_ind => $room) {
                                            //return response()->json($room,200);
                                            foreach ($room->rates as $rate) {
                                                if ($service_room['hyperguest_pull'] == 1) {
                                                    if ($rate->ratePlanId == $service_room['rate_plan_id'] && $room->room_id == $service_room['room_id']) {
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
                                                                    // 'quantity_adults' => 1,
                                                                    'quantity_adults' => 2,
                                                                    'quantity_child' => 0,
                                                                ],
                                                            ],
                                                            // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                            // 'rate_plan' => $service_room["rate_plan"],
                                                        ]);

                                                        for ($i = 0; $i < $quantity_rooms_dbl; $i++) {

                                                            $node_reservations = [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                // "rate_plan_room_id" => $rate->ratePlanId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "only_on_request" => $only_on_request,
                                                                // "quantity_adults" => $quantity_adults,
                                                                "quantity_adults" => 2,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => [],
                                                                // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                                // 'rate_plan' => $service_room["rate_plan"],
                                                            ];
                                                            array_push(
                                                                $reservation_data["reservations"],
                                                                $node_reservations
                                                            );
                                                            $validateHotel[$result_hotel->token_search][] = $node_reservations;
                                                            $seReservoHotel = true;
                                                        }
                                                        break;
                                                    }
                                                }else {
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
                                                            // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                            // 'rate_plan' => $service_room["rate_plan"],
                                                        ]);
                                                        for ($i = 0; $i < $quantity_rooms_tpl; $i++) {

                                                            $node_reservations = [
                                                                "token_search" => $result_hotel->token_search,
                                                                "room_ident" => 0,
                                                                "hotel_id" => $hotel_id,
                                                                "best_option" => false,
                                                                "rate_plan_room_id" => $rate->rateId,
                                                                // "rate_plan_room_id" => $rate->ratePlanId,
                                                                "suplements" => [],
                                                                "guest_note" => "",
                                                                "date_from" => $check_in,
                                                                "date_to" => $check_out,
                                                                "only_on_request" => $only_on_request,
                                                                // "quantity_adults" => $quantity_adults,
                                                                "quantity_adults" => 3,
                                                                "quantity_child" => $quantity_child,
                                                                "child_ages" => [],
                                                                // 'hyperguest_pull' => $service_room["hyperguest_pull"],
                                                                // 'rate_plan' => $service_room["rate_plan"],
                                                            ];
                                                            array_push(
                                                                $reservation_data["reservations"],
                                                                $node_reservations
                                                            );
                                                            $validateHotel[$result_hotel->token_search][] = $node_reservations;
                                                            $seReservoHotel = true;
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ($seReservoHotel == false) {
                            array_push($hotelesNoReservadoPaquete, $hotel_id);
                        }
                    } catch (\Exception $e) {
                        throw new \Exception(json_encode($e));
                    }
                }

                //Todo Servicios
                if ($service["type"] == "service") {
                    $services_id = [$service["object_id"]];
                    $service_id = $service["object_id"];
                    $check_in = $service["date_in"];

                    $destinationsRequest = Request::createFrom($request);

                    $destinationsRequest->request->add([
                        'client_id' => $client_id,
                        'services_id' => $services_id,
                        'with_period' => Carbon::parse($check_in)->year,
                    ]);

                    $servicesRequest = ServiceRequest::createFrom($request);

                    $servicesRequest->request->add([
                        'client_id' => (int)$client_id,
                        'lang' => 'es',
                        'supplements' => false,
                        'date' => $check_in,
                        'services_id' => $services_id,
                        'quantity_persons' => [
                            "adults" => (int)$quantity_adults,
                            "child" => (int)$quantity_child,
                            "age_childs" => $ages_child,
                        ],
                    ]);

                    $service_search = $this->services($servicesRequest);
                    $result_service = $service_search->getData()->data;
                    $response = json_decode($service_search->getContent(), true);

                    if ($response['success'] == false) {
                        return Response::json([
                            'success' => false,
                            "message" => $response['message'],
                            "error" => $response['error'],
                        ]);
                        die;
                    }

                    if (count($result_service->services) == 0) {
                        $service['child'] = $quantity_child;
                        $validation_errors['services']->add($this->validationsQuoteServices(
                            $service,
                            $client_id,
                            [],
                            'Package'
                        ));
                        continue;
                    }

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
                        'child_ages' => [],
                    ]);
                }
                //Todo Vuelos
                if ($service["type"] == "flight") {
                    $services_id = [$service["object_id"]];
                    $service_id = $service["object_id"];
                    $date_in = Carbon::parse($service["date_in"])->format('d/m/Y');
                    $check_in = Carbon::createFromFormat('d/m/Y', $date_in)->format('Y-m-d');
                    array_push($reservation_data["reservations_flights"], [
                        'service_id' => $service_id,
                        'client_id' => (int)$client_id,
                        'origin' => $service["origin"],
                        'destiny' => $service["destiny"],
                        'code_flight' => $service["code_flight"],
                        'lang' => 'es',
                        'date' => $check_in,
                        'services_id' => $services_id,
                        'quantity_persons' => [
                            "adults" => (int)$quantity_adults,
                            "child" => (int)$quantity_child,
                            "ages_child" => [
                                [
                                    'child' => 1,
                                    'age' => 1,
                                ],
                            ],
                        ],
                    ]);
                }
            } catch (\Exception $e) {
                return Response::json([
                    'success' => false,
                    'message' => 'object_id: ' . $service["object_id"] . ' type : ' . $service["type"],
                    'error' => $e->getMessage() . ' - Line:' . $e->getLine(),
                ]);
            }
        }

        // Validamos que la cantidad de habitaciones sea igual a lo que se ha seleccionado en el paquete..

        $totalPaxReservas = $quantity_adults + $quantity_child;
        foreach ($validateHotel as $tocken => $reservas) {

            $pax = 0;
            foreach ($reservas as $row) {
                $pax = $pax + $row['quantity_adults'] + $row['quantity_child'];
            }

            if ($pax != $totalPaxReservas) {

                $validation_errors['hotels']->add($this->validateHotelPax($row));
            }
        }
        // dd($validateHotel,$totalPaxReservas);
        if (count($hotelesNoReservadoPaquete) > 0) {

            foreach ($hotelesNoReservadoPaquete as $hotel_reserva) {
                $validation_errors['hotels']->add($this->crearNodoError($hotel_reserva));
            }
        }

        // return Response::json([
        //     'success' => false,
        //     'message' => $hotelesNoReservadoPaquete,
        //     'error' => "error hoteles",
        // ]);

        //Todo Agregamos los servicios fuera del paquete
        foreach ($services_added_package as $service) {
            $assigned_passengers = [];
            if (isset($service['paxs'])) {
                foreach ($service['paxs'] as $index => $pax) {
                    if ($pax) {
                        array_push($assigned_passengers, $index + 1);
                    }
                }
            }
            array_push($reservation_data["reservations_services"], [
                'token_search' => $service['token_search'],
                'service_ident' => 0,
                'service_id' => $service['id'],
                'rate_plan_id' => $service['rate_plan_id'],
                'reservation_time' => '00:00',
                'date_from' => $date,
                "guest_note" => "",
                'quantity_adults' => $service['quantity_adults'],
                'quantity_child' => 0,
                'assigned_passengers' => $assigned_passengers,
                'child_ages' => [],
            ]);
        }

        $data = [
            'success' => true,
            'data' => $reservation_data,
        ];

        if ($validation_errors['hotels']->count() > 0 or $validation_errors['services']->count() > 0) {
            return Response::json(['success' => false, 'errors' => $validation_errors]);
        }

        return response()->json($data, 200);
    }

    public function crearNodoError($hotel_id)
    {

        $hotel_validate = Hotel::where('id', $hotel_id)
            ->withTrashed()
            ->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                },
            ])
            ->first(['id', 'name']);

        $validations = collect([
            "hotel_code" => $hotel_validate->channels[0]->pivot->code,
            "name" => $hotel_validate->name,
            "errors" => collect(),
        ]);

        $validations['errors']->add([
            "error" => trans('validations.quotes.hotels.hotel_paquete_incomplete'),
        ]);

        return $validations;
    }

    public function validateHotelPax($service_hotel)
    {

        $hotel_validate = Hotel::where('id', $service_hotel['hotel_id'])
            ->withTrashed()
            ->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                },
            ])
            ->first(['id', 'name']);

        $validations = collect([
            "hotel_code" => $hotel_validate->channels[0]->pivot->code,
            "name" => $hotel_validate->name,
            "errors" => collect(),
        ]);

        $validations['errors']->add([
            "error" => trans('validations.quotes.hotels.hotel_pax_incomplete'),
        ]);

        return $validations;
    }

    public function validationsQuoteServices($service, $client_id, $services_age_children = [], $from = 'Quote')
    {
        $totalPax = $service['adult'] + $service['child'];
        if ($from == 'Quote') {
            $date_in = Carbon::createFromFormat('d/m/Y', $service['date_in'])->format('Y-m-d');
        } else {
            $date_in = Carbon::parse($service['date_in'])->format('Y-m-d');
        }

        $year = Carbon::parse($date_in)->format('Y');
        $lang = config('app.locale') ?? 'en';
        $language_id = Language::where('iso', '=', $lang)->value('id');

        if (!$language_id) {
            $language_id = 1;
        }

        $service_validate = Service::where('id', $service['object_id'])
            ->with([
                'service_translations' => function ($query) use ($language_id) {
                    $query->select(['name', 'id', 'service_id']);
                    $query->where('language_id', '=', $language_id);
                },
                'service_rate' => function ($query) use ($service, $totalPax, $date_in) {
                    $query->select('id', 'name', 'service_id', 'status');
                    $query->where('status', 1);
                    $query->with([
                        'service_rate_plans' => function ($query) use ($service, $totalPax, $date_in) {
                            $query->where('date_from', '<=', $date_in)
                                ->where('date_to', '>=', $date_in);
                            $query->where('pax_from', '<=', $totalPax)
                                ->where('pax_to', '>=', $totalPax)
                                ->where('status', 1);
                        },
                    ]);
                    $query->with([
                        'inventory' => function ($query) use ($date_in, $totalPax) {
                            $query->select([
                                'id',
                                'service_rate_id',
                                'day',
                                'date',
                                'inventory_num',
                                'total_booking',
                                'total_canceled',
                                'locked',
                            ]);
                            $query->where('date', '>=', $date_in);
                            $query->where('date', '<=', $date_in);
                        },
                    ]);
                },
            ])
            ->with([
                'children_ages' => function ($query) {
                    $query->select(['min_age', 'max_age', 'service_id']);
                },
            ])
            ->withTrashed()
            ->first([
                'id',
                'aurora_code',
                'allow_child',
                'allow_infant',
                'infant_min_age',
                'infant_max_age',
                'pax_min',
                'pax_max',
                'status',
                'deleted_at',
            ]);

        $validations = collect([
            "service_code" => $service_validate->aurora_code,
            "name" => $service_validate->service_translations[0]['name'],
            "errors" => collect(),
        ]);

        //Todo: Verifico si el servicio ha sido eliminado
        if ($service_validate->deleted_at !== null) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.services.service_deleted'),
            ]);
        } else {
            //Todo: Verifico si el servicio ha sido desactivado
            if ($service_validate->status === 0) {
                $validations['errors']->add([
                    "error" => trans('validations.quotes.services.service_status_disabled'),
                ]);
            }

            //Todo: Verifico si el servicio tiene la capacidad de pasajeros permitido
            if ($totalPax > $service_validate->pax_max) {
                $validations['errors']->add([
                    "error" => trans(
                        'validations.quotes.services.service_maximum_pax_exceeded',
                        ['pax' => $service_validate->pax_max]
                    ),
                ]);
            }

            //Todo: Verifico si el servicio permite niños
            if ($service['child'] > 0 && $service_validate->allow_child === 0) {
                $validations['errors']->add([
                    "error" => trans('validations.quotes.services.service_child_not_allowed'),
                ]);
            }

            if ($service['child'] > 0 and count($services_age_children) > 0 and ($service_validate->allow_child === 1 or $service_validate->allow_infant === 1)) {
                if ($service_validate->children_ages->count() === 0) {
                    $validations['errors']->add([
                        "error" => trans('validations.quotes.services.service_child_not_insert'),
                    ]);
                } else {
                    $children_ages = $service_validate->children_ages->first();
                    $validate_age_child = false;
                    foreach ($services_age_children as $age) {
                        if (($children_ages->min_age <= $age['age'] and $children_ages->max_age >= $age['age'] and $service_validate->allow_child === 1) or
                            ($service_validate->infant_min_age <= $age['age'] and $service_validate->infant_max_age >= $age['age'] and $service_validate->allow_infant === 1)
                        ) {
                            $validate_age_child = false;
                        } else {
                            $validate_age_child = true;
                            break;
                        }
                    }

                    if ($validate_age_child) {
                        if ($service_validate->allow_infant === 1) {
                            $validations['errors']->add([
                                "error" => trans(
                                    'validations.quotes.services.service_infant_not_age_allowed',
                                    [
                                        'min_age' => $service_validate->infant_min_age,
                                        'max_age' => $service_validate->infant_max_age,
                                    ]
                                ),
                            ]);
                        }
                        if ($service_validate->allow_child === 1) {
                            $validations['errors']->add([
                                "error" => trans(
                                    'validations.quotes.services.service_child_not_age_allowed',
                                    [
                                        'min_age' => $children_ages->min_age,
                                        'max_age' => $children_ages->max_age,
                                    ]
                                ),
                            ]);
                        }
                    }
                }
            }

            //Todo: Verifico si el servicio tiene tarifa para el rango de pax
            $service_rate_count = $service_validate->service_rate->count();
            if ($service_rate_count === 0) {
                $validations['errors']->add([
                    "error" => trans('validations.quotes.services.service_not_found_rate'),
                ]);
            } else {
                $service_rate_plan_count = $service_validate->service_rate[0]->service_rate_plans->count();
                if ($service_rate_plan_count === 0) {
                    $validations['errors']->add([
                        "error" => trans('validations.quotes.services.service_not_found_rate'),
                    ]);
                }
            }

            //Todo Validamos la tarifa
            $rate_plan_model = ServiceRate::find($service_validate->service_rate[0]->id);
            if ($rate_plan_model) {
                //Todo Validamos la tarifa no este bloqueada para el cliente
                $client_rate_plans = ServiceClientRatePlan::where('client_id', $client_id)
                    ->where('period', $year)
                    ->where('service_rate_id', $rate_plan_model->id)
                    ->first(['id', 'client_id', 'service_rate_id']);
                if ($client_rate_plans) {
                    $validations['errors']->add([
                        "error" => trans(
                            'validations.quotes.services.service_rate_disabled_for_client',
                            ['rate_name' => '"(' . $rate_plan_model->id . ') ' . $rate_plan_model->name . '"']
                        ),
                    ]);
                }
            }

            $day_inventory_locked = false;
            if ($service_validate->service_rate[0]->inventory->count() > 0) {
                if ($service_validate->service_rate[0]->inventory[0]->locked == 1) {
                    $day_inventory_locked = true;
                }
            }

            if ($day_inventory_locked) {
                $validations['errors']->add([
                    "error" => trans('validations.quotes.services.service_day_availability', ['date' => $date_in]),
                ]);
            }
        }

        if (count($validations['errors']) == 0) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.services.service_pre_validate'),
            ]);
        }

        return $validations;
    }

    public function validationsQuoteHotels($hotel, $client_id, $date, $service_rooms)
    {
        // dd($service_rooms->toArray());
        $year = Carbon::parse($date)->format('Y');

        $hotel_validate = Hotel::where('id', $hotel['object_id'])
            ->withTrashed()
            ->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                },
            ])
            ->first(['id', 'name', 'allows_child', 'status', 'deleted_at']);

        $validations = collect([
            "hotel_code" => $hotel_validate->channels[0]->pivot->code,
            "name" => $hotel_validate->name,
            "errors" => collect(),
        ]);

        if (!empty($hotel_validate->deleted_at)) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.hotels.hotel_deleted'),
            ]);
        }

        if ($hotel_validate->status == 0) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.hotels.hotel_status_disabled'),
            ]);
        }

        //Todo Validamos si el hotel esta bloqueado para el cliente
        $hotel_blocked = HotelClient::where('period', $year)
            ->where('client_id', $client_id)
            ->where('hotel_id', $hotel['object_id'])
            ->first();
        if ($hotel_blocked) {
            $hotel = Hotel::find($hotel['object_id'], ['id', 'name']);
            $validations['errors']->add([
                "error" => trans(
                    'validations.quotes.hotels.hotel_blocked_for_client',
                    ['hotel_name' => '"(' . $hotel->id . ') ' . $hotel->name . '"']
                ),
            ]);
        } else {
            //Todo Buscamos si las tarifas estan bloqueadas
            if (count($service_rooms) > 0) {
                foreach ($service_rooms as $service_room) {
                    // Validamos si el hotel es hyperguest pull ()
                    if ($service_room['hyperguest_pull'] == 0){
                        $rate_plan_room_model = RatesPlansRooms::where('id', $service_room["rate_plan_room_id"])
                            ->first(['id', 'rates_plans_id', 'room_id', 'status']);
                        //Todo Validamos la habitacion
                        if ($rate_plan_room_model) {

                            //Todo Validamos el estado de la habitacion
                            if ($rate_plan_room_model->status == 0) {
                                $validations['errors']->add([
                                    "error" => trans(
                                        'validations.quotes.hotels.hotel_room_status_disabled',
                                        ['room_id' => $rate_plan_room_model->id]
                                    ),
                                ]);
                            }

                            //Todo Validamos la tarifa
                            $rate_plan_model = RatesPlans::where('id', $rate_plan_room_model->rates_plans_id)
                                ->first(['id', 'name', 'code', 'status']);

                            if ($rate_plan_model) {

                                //Todo Validamos el estado de la tarifa
                                if ($rate_plan_model->status == 0) {
                                    $validations['errors']->add([
                                        "error" => trans(
                                            'validations.quotes.hotels.hotel_rate_status_disabled',
                                            ['room_id' => $rate_plan_model->id]
                                        ),
                                    ]);
                                }

                                //Todo Validamos la tarifa no este bloqueada para el cliente
                                $client_rate_plans = ClientRatePlan::where('client_id', $client_id)
                                    ->where('period', $year)
                                    ->where('rate_plan_id', $rate_plan_room_model['rates_plans_id'])
                                    ->first(['id', 'client_id', 'rate_plan_id']);
                                if ($client_rate_plans) {
                                    $validations['errors']->add([
                                        "error" => trans(
                                            'validations.quotes.hotels.hotel_rate_disabled_for_client',
                                            ['rate_name' => '"(' . $rate_plan_model->id . ') ' . $rate_plan_model->name . '"']
                                        ),
                                    ]);
                                }
                            } else {
                                $validations['errors']->add([
                                    "error" => trans(
                                        'validations.quotes.hotel_rate_deleted',
                                        ['room_id' => $rate_plan_room_model->rates_plans_id]
                                    ),
                                ]);
                            }
                        } else {
                            $validations['errors']->add([
                                "error" => trans(
                                    'validations.quotes.hotel_room_deleted',
                                    ['room_id' => $rate_plan_room_model->id]
                                ),
                            ]);
                        }
                    }else {
                        // VALIDACIONES DE HYPERGUEST

                    }
                }
            }
        }

        if ($validations['errors']->count() === 0) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.no_results_found'),
            ]);
        }

        return $validations;
    }

    public function validateRoomsHotel($hotel, $verify_available_room)
    {
        $hotel_validate = Hotel::where('id', $hotel['object_id'])
            ->withTrashed()
            ->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                },
            ])
            ->first(['id', 'name', 'allows_child', 'status', 'deleted_at']);

        $validations = collect([
            "hotel_code" => $hotel_validate->channels[0]->pivot->code,
            "name" => $hotel_validate->name,
            "errors" => collect(),
        ]);

        if ($hotel['simple'] > 0 and !$verify_available_room['simple']) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.hotels.hotel_simple_room_type_not_found'),
            ]);
        }

        if ($hotel['double'] > 0 and !$verify_available_room['double']) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.hotels.hotel_double_room_type_not_found'),
            ]);
        }

        if ($hotel['triple'] > 0 and !$verify_available_room['triple']) {
            $validations['errors']->add([
                "error" => trans('validations.quotes.hotels.hotel_triple_room_type_not_found'),
            ]);
        }

        $validate_room = collect();
        if ($validations['errors']->count() > 0) {
            $validate_room = $validations;
        }
        return $validate_room;
    }

    protected function convertToHotelSearchRequest(array $params)
    {
        $hotelSearchRequest = new HotelSearchRequest();
        $hotelSearchRequest->replace($params);
        // Validar los datos
        // $hotelSearchRequest->validateResolved();
        return $hotelSearchRequest;
    }

    public function validateRoomsHotelV2($hotel, $verify_available_room, array $context = [])
    {
        $hotel_validate = Hotel::where('id', $hotel['object_id'])
            ->withTrashed()
            ->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                },
            ])
            ->first(['id', 'name', 'allows_child', 'status', 'deleted_at']);

        $hotelCode = '';
        if ($hotel_validate && isset($hotel_validate->channels[0]) && isset($hotel_validate->channels[0]->pivot)) {
            $hotelCode = (string) $hotel_validate->channels[0]->pivot->code;
        }

        $validations = collect([
            "hotel_code" => $hotelCode,
            "name" => $hotel_validate ? $hotel_validate->name : '',
            "errors" => collect(),
        ]);

        // Contexto
        $checkIn  = $context['check_in']  ?? null;
        $checkOut = $context['check_out'] ?? null;
        $nights   = $context['nights']    ?? null;
        $channel  = $context['channel']   ?? null;
        $on_request = $context['on_request'] ?? false;
        // $rateIds  = $context['rate_plan_room_ids'] ?? [];

        // Si es tarifa AURORA, se permite pasar la reserva sin validar disponibilidad instantánea
        if (strpos($channel, 'AURORA') !== false) {
            return collect();
        }

        // Si es tarifa HYPERGUEST y es On Request (RQ), se permite pasar la reserva
        if (strpos($channel, 'HYPERGUEST') !== false && $on_request) {
            return collect();
        }

        // Sufijo descriptivo (sin romper traducciones)
        $suffixParts = [];
        // if ($hotelCode !== '') $suffixParts[] = "Hotel: {$hotelCode}";
        if (!empty($channel)) $suffixParts[] = "Tarifa: {$channel}";
        if (!empty($checkIn) && !empty($checkOut)) $suffixParts[] = "Fechas: {$checkIn} → {$checkOut}";
        if (!empty($nights)) $suffixParts[] = "Noches: {$nights}";
        // if (!empty($rateIds)) $suffixParts[] = "RatesPlansRooms: " . implode(',', array_unique($rateIds));

        $suffix = count($suffixParts) ? ' (' . implode(' | ', $suffixParts) . ')' : '';

        $addError = function (string $translationKey, string $roomTypeLabel) use (&$validations, $suffix) {
            $baseMsg = trans($translationKey);
            $validations['errors']->add([
                "error" => $baseMsg . $suffix . " | Habitación: {$roomTypeLabel}",
            ]);
        };

        if (($hotel['simple'] ?? 0) > 0 && empty($verify_available_room['simple'])) {
            $addError('validations.quotes.hotels.hotel_simple_room_type_not_found', 'Simple');
        }

        if (($hotel['double'] ?? 0) > 0 && empty($verify_available_room['double'])) {
            $addError('validations.quotes.hotels.hotel_double_room_type_not_found', 'Doble');
        }

        if (($hotel['triple'] ?? 0) > 0 && empty($verify_available_room['triple'])) {
            $addError('validations.quotes.hotels.hotel_triple_room_type_not_found', 'Triple');
        }

        return ($validations['errors']->count() > 0) ? $validations : collect();
    }


}
