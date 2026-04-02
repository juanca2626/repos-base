<?php

namespace App\Http\Controllers;

use App\City;
use App\Client;
use App\ClientRatePlan;
use App\ClientSeller;
use App\CloneLog;
use App\DateRangeHotel;
use App\Exports\ServiceNotesExport;
use App\Hotel;
use App\HotelRateOrderCity;
use App\Http\Stella\StellaService;
use App\Http\Traits\Quotes;
use App\Imports\ImportablesImport;
use App\Jobs\ExportRatesServicesByYear;
use App\Jobs\ExportRatesServicesByYearTest;
use App\Language;
use App\LitoExtensionFile;
use App\LogRate;
use App\Markup;
use App\Module;
use App\Package;
use App\PoliciesRates;
use App\Quote;
use App\QuoteLog;
use App\Reservation;
use App\ReservationsService;
use App\Room;
use App\Service;
use App\ServiceRatePlan;
use App\State;
use App\Translation;
use App\TypeClass;
use App\User;
use App\Country;
use App\Doctype;
use App\ConfigMarkup;
use App\Imports\ReadPassengersImport;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    use Quotes;

    public function rangesExport($quote_id, Request $request)
    {
        $user_type_id = $request->get('user_type_id');
        $user_id = $request->get('user_id');
        $client_id = null;
        if ($user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', $user_id)->first();
            $client_id = $client_id["client_id"];
        }

        if ($user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        $this->updateAmountAllServices($quote_id, $client_id);
        $lang = $request->get('lang');
        $quote_name = 'THE QUOTE HAS NO NAME';
        $quote = Quote::where('id', $quote_id)->first();
        if ($quote) {
            $quote_name = $quote->name;
        }
        $quote_name = str_replace("/", "-", $quote_name);
        return Excel::download(new  \App\Exports\CategoryExport($quote_id, $client_id, $lang), $quote_name . '.xlsx');
    }

    public function passengersExport($quote_id, Request $request)
    {
        try {
            $user_type_id = $request->get('user_type_id');
            $user_id = $request->get('user_id');
            $lang = $request->get('lang');
            $client_id = null;

            if (empty($user_type_id)) {
                return response()->json("No se envió el tipo de usuario, por favor intente nuevamente..", 500);
            }

            if (empty($user_id)) {
                return response()->json("No se envió el id de usuario, por favor intente nuevamente..", 500);
            }

            if (empty($lang)) {
                return response()->json("No se envió el idioma, por favor intente nuevamente..", 500);
            }

            if ($user_type_id == 4) {
                $client_id = ClientSeller::select('client_id')->where('user_id', $user_id)->first();
                $client_id = $client_id["client_id"];
            }

            if ($user_type_id == 3) {
                $client_id = $request->post('client_id');
            }
            $quote_name = \App\Quote::where('id', $quote_id)->first()->name;
            $quote_name = str_replace("/", "-", $quote_name);
            return Excel::download(new  \App\Exports\CategoryPassengerExport($quote_id, $client_id, $lang),
                $quote_name . '.xlsx');
        } catch (\Exception $e) {
            $error = $this->throwError($e);
            return response()->json($error, 500);
        }
    }

    public function galleriesExport(Request $request)
    {
        $services = Service::where('status', '=', 1)->whereDoesntHave('galleries', function ($query) {
            $query->where('type', 'service')
                ->where('slug', 'service_gallery');
        })->get();

        $hotels = Hotel::with('channels')->where('status', '=', 1)->whereDoesntHave('galeries', function ($query) {
            $query->where('type', 'hotel')
                ->where('slug', 'hotel_gallery');
        })->get();

        return Excel::download(new  \App\Exports\RequestAurora\RequestHotelWithOutImages($services, $hotels),
            'hotels_services_galleries.xlsx');
    }

    public function hotelsLocationExport(Request $request)
    {
        $hotels = Hotel::where('status', '=', 1)->with([
            'country.translations',
            'state.translations',
            'city.translations',
            'district.translations',
            'zone.translations'
        ])->get()->toArray();

        return Excel::download(new  \App\Exports\LocationExport($hotels, 'hotel'),
            'hotels_locations_export.xlsx');
    }

    public function servicesLocationExport(Request $request)
    {
        $services = Service::where('status', '=', 1)->with([
            'serviceOrigin',
            'serviceDestination',
            'serviceOrigin.city.translations',
            'serviceOrigin.country.translations',
            'serviceOrigin.state.translations',
            'serviceOrigin.zone.translations',
            'serviceDestination.city.translations',
            'serviceDestination.country.translations',
            'serviceDestination.state.translations',
            'serviceDestination.zone.translations',
        ])->get()->toArray();

        // return response()->json($services);

        return Excel::download(new  \App\Exports\LocationExport($services, 'service'),
            'services_locations_export.xlsx');
    }

    public function hotelsPointsExport(Request $request)
    {
        $hotels = Hotel::where('status', '=', 1)->where(function ($query) {
            $query->orWhere('zone_id', '=', '');
            $query->orWhereNull('zone_id');
        })->get();

        return Excel::download(new  \App\Exports\LocationExport($hotels, 'point'),
            'hotels_locations_export.xlsx');
    }

    public function translationExport($module_id)
    {
        $module = Module::find($module_id);

        return Excel::download(new  \App\Exports\TranslationModuleExport($module_id), $module->name . '.xlsx');
    }

    public function translationInclusionExport()
    {

        return Excel::download(new  \App\Exports\TranslationInclusionExport(), 'Inclusiones' . '.xlsx');
    }

    public function translationOperativityExport()
    {

        return Excel::download(new  \App\Exports\TranslationOperativityExport(), 'Operatividades' . '.xlsx');
    }

    public function translationAmenitiesExport()
    {

        return Excel::download(new  \App\Exports\TranslationAmenitiesExport(), 'Amenidades' . '.xlsx');
    }

    public function translationRemarksExport()
    {

        return Excel::download(new  \App\Exports\TranslationRemarksExport(), 'Remarks' . '.xlsx');
    }

    public function serviceExportYear($service_year, Request $request)
    {

        $client_id = $this->getClientId($request);
        $lang = $request->has('lang') ? $request->input('lang') : 'es';

        $log_rate_validate = LogRate::where('user_id', Auth::id())
            ->where('type', 'service')
            ->where('year', $service_year)
            ->where('document_job_status', 0)->count();

        if ($log_rate_validate > 0) {
            return Response::json(['success' => false, 'status_download' => true]);
        }


        $user = User::find(Auth::id());
        $client = Client::find($client_id);

        $log_rate = new LogRate();
        $log_rate->type = "service";
        $log_rate->year = $service_year;
        $log_rate->user_id = $user->id;
        $log_rate->user_email = $user->email;
        $log_rate->client_id = $client_id;
        $log_rate->client_code = $client->code;
        $log_rate->save();
        $faker = Faker::create();
        $document_name_store = $faker->unique()->uuid;

        ExportRatesServicesByYear::dispatch($service_year, $lang, $client_id, $user, $document_name_store,
            $log_rate->id);

        return Response::json(['success' => true]);
    }

    public function serviceExportYearTest($service_year, Request $request)
    {
        $client_id = $this->getClientId($request);
        $lang = $request->has('lang') ? $request->input('lang') : 'es';

        $log_rate_validate = LogRate::where('user_id', Auth::id())
            ->where('type', 'service')
            ->where('year', $service_year)
            ->where('document_job_status', 0)->count();

        if ($log_rate_validate > 0) {
            return Response::json(['success' => false, 'status_download' => true]);
        }


        $user = User::find(Auth::id());
        $client = Client::find($client_id);

        $log_rate = new LogRate();
        $log_rate->type = "service";
        $log_rate->year = $service_year;
        $log_rate->user_id = $user->id;
        $log_rate->user_email = $user->email;
        $log_rate->client_id = $client_id;
        $log_rate->client_code = $client->code;
        $log_rate->save();
        $faker = Faker::create();
        $document_name_store = $faker->unique()->uuid;

        ExportRatesServicesByYearTest::dispatch($service_year, $lang, $client_id, $user, $document_name_store,
            $log_rate->id);

        return Response::json(['success' => true]);
//        $client_id = $this->getClientId($request);
//        $lang = $request->has('lang') ? $request->input('lang') : 'es';
//        $user = User::find(Auth::id());
//        $faker = Faker::create();
//        $document_name_store = $faker->unique()->uuid;
//        $services = new Service();
//        return Response::json($services->ratesServicesByYearUpdate($service_year, $lang, $client_id));
    }


    public function hotelExportYear($hotel_year, Request $request)
    {
        $client_id = $this->getClientId($request);
        $user_id = $request->get('user_id');
        if (empty($user_id) || $user_id == 'undefined' || $user_id == '0') {
            $user_id = Auth::id();
        }
        $user = User::find($user_id);
        if ($user) {
            $client = Client::find($client_id);
            $log_rate = new LogRate();
            $log_rate->type = "hotel";
            $log_rate->year = $hotel_year;
            $log_rate->user_id = $user->id;
            $log_rate->user_email = $user->email;
            $log_rate->client_id = $client_id;
            $log_rate->client_code = $client->code;
            $log_rate->save();
            return Excel::download(new  \App\Exports\HotelCityExport($hotel_year, $request->input('lang'), $client_id),
                'Hoteles_' . $hotel_year . '.xlsx');
        } else {
            return response()->json([
                'type' => 'error',
                'message' => 'No se encontro el usuario'
            ]);
        }

    }

    public function hotelGenerateArray($hotel_year, Request $request)
    {
        try {
            set_time_limit(0);

            $client_id = $this->getClientId($request);

            $service_year = $hotel_year;
            $language_id = Language::where('state', 1)->where('iso', $request->input('lang'))->first()->id;

            $data = [
                "cities" => []
            ];
            $th_excel = Translation::select('value')->where('type', 'label')->where('slug', 'LIKE',
                '%export_excel_hotel%')->where('language_id', $language_id)->pluck('value');

            $markup = 0;
            /* $markup_rate_client = DB::table('markup_services')
                 ->where('client_id', $client_id)
                 ->where('service_id', $quote_service->object_id)
                 ->where('period', Carbon::parse($date_in)->year)
                 ->first();*/
            $client_rate_plans_id = [];
            if ($client_id != 15766) { // 5NEG
                $client_rate_plans_id = ClientRatePlan::where('client_id', $client_id)->where('period',
                    $service_year)->pluck('rate_plan_id')->toArray();
            }
            $markup = Markup::where('client_id', $client_id)->where('period', $service_year)->first()->hotel;
            //Service Types
            $client = Client::where('id', $client_id)->first();

            //Service Categories
            $class_hotels_new = [];
            $class_hotels = TypeClass::with([
                'translations' => function ($query) use ($language_id) {
                    $query->where('type', 'typeclass');
                    $query->where('language_id', $language_id);
                }
            ])
                ->orderBy('order')
                ->get();
            foreach ($class_hotels as $class) {
                array_push($class_hotels_new, [
                    "hotel_class_id" => $class["id"],
                    "hotel_class_name" => $class["translations"][0]["value"],
                    "color" => $class["color"],
                    "hotels" => []
                ]);
            }

            $states_ids = State::where('country_id', 89)->pluck("id");
            $cities_ids = City::whereIn("state_id", $states_ids)->pluck("id");

            $cities_ = HotelRateOrderCity::whereIn("city_id", $cities_ids)
                ->with([
                    "translations" => function ($query) use ($language_id) {
                        $query->where('type', 'city');
                        $query->where('language_id', $language_id);
                    }
                ])
                ->orderBy("order")
//                ->take(1)
                ->get();

            foreach ($cities_ as $city) {
                array_push($data["cities"], [
                    "city_id" => $city["city_id"],
                    "city_name" => $city["translations"][0]["value"],
                    "note_1" => trans('export_excel.note_1', [], $request->input('lang')),
                    "note_2" => trans('export_excel.note_2', [], $request->input('lang')),
                    "note_3" => trans('export_excel.note_3', [], $request->input('lang')),
                    "note_4" => trans('export_excel.note_4', [], $request->input('lang')),
                    "date_download" => Carbon::now()->format('d/m/Y'),
                    "client_code" => $client->code,
                    "client_name" => $client->name,
                    "categories" => $class_hotels_new,
                    "th" => $th_excel
                ]);
            }

            //End arreglo de ciudades

            $hotels = Hotel::with([
                'hoteltypeclass' => function ($query) use ($service_year) {
                    $query->where('year', $service_year);
                },
                'rooms' => function ($query) use ($language_id) {
                    $query->orderBy("order");
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('type', 'room');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with('room_type');
                    $query->with([
                        'rates_plan_room' => function ($query) use ($language_id) {
                            $query->with([
                                'rate_plan' => function ($query) use ($language_id) {
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->where('type', 'rates_plan');
                                            $query->where('slug', 'commercial_name');
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
//                                    $query->where('status',1);
                                    $query->where('rate', 1);
                                }
                            ]);

                            $query->where('channel_id', 1);
                            $query->where('status', 1);
                        }
                    ]);
                    $query->where('state', 1);
                    $query->where('see_in_rates', 1);
                }
            ])->with([
                'channel' => function ($query) {
                    $query->orderBy('channel_id');
                }
            ])->where('status', 1)
//            ->where('id', 169)
                ->orderBy("rate_order")
                ->get();

//            return $hotels;

//            return response()->json($hotels);

            $hotels_new = [];

            foreach ($hotels as $hotel) {
                $typeclass_id = $hotel["typeclass_id"];
                if ($hotel->hoteltypeclass->count() > 0) {
                    $typeclass_id = $hotel->hoteltypeclass[0]->typeclass_id;
                }

                array_push($hotels_new, [
                    "aurora_code" => $hotel["channel"][0]["code"],
                    "hotel_id" => $hotel["id"],
                    "hotel_city_id" => $hotel["city_id"],
                    "hotel_type_class_id" => $typeclass_id,
                    "hotel_name" => $hotel["name"],
                    "hotel_rooms" => []
                ]);
                //Add Hotel Rooms
                foreach ($hotel["rooms"] as $room) {
                    array_push($hotels_new[count($hotels_new) - 1]["hotel_rooms"], [
                        "room_id" => $room["id"],
                        "room_name" => optional($room["translations"]->firstWhere('slug', 'room_name'))->value ?? '',
                        "rates_plan_room" => []
                    ]);
                    //Add Rate Plans
                    foreach ($room["rates_plan_room"] as $rate_plan_room) {
//                        try {
                        if (isset($rate_plan_room["rate_plan"])) {
                            if (count($rate_plan_room["rate_plan"]["translations"]) > 0 and $rate_plan_room["rate_plan"]["translations"][0]["value"] != "Tarifa Base Estela" &&
                                (int)$rate_plan_room["rate_plan"]["rate"] == 1
                            ) { // Regulares
//                                    dd($rate_plan_room["rate_plan"]["translations"][0]);
                                $rate_plan_name = (count($rate_plan_room["rate_plan"]["translations"])) > 0 ? $rate_plan_room["rate_plan"]["translations"][0]["value"] : 'NULL';

                                if (count($client_rate_plans_id) > 0) {
                                    if (!in_array($rate_plan_room["rate_plan"]["id"], $client_rate_plans_id)) {
                                        array_push($hotels_new[count($hotels_new) - 1]["hotel_rooms"][(count($hotels_new[count($hotels_new) - 1]["hotel_rooms"]) - 1)]["rates_plan_room"],
                                            [
                                                "rate_plan_room_id" => $rate_plan_room["id"],
                                                "rate_plan_id" => $rate_plan_room["rate_plan"]["id"],
                                                "rate_plan_name" => $rate_plan_name,
                                                "calendars" => []
                                            ]);
                                    }
                                } else {
                                    array_push($hotels_new[count($hotels_new) - 1]["hotel_rooms"][count($hotels_new[count($hotels_new) - 1]["hotel_rooms"]) - 1]["rates_plan_room"],
                                        [
                                            "rate_plan_room_id" => $rate_plan_room["id"],
                                            "rate_plan_id" => $rate_plan_room["rate_plan"]["id"],
                                            "rate_plan_name" => $rate_plan_name,
                                            "calendars" => []
                                        ]);
                                }

                            }
                        }
//                        } catch (\Exception $exception) {
//                            dd($room["translations"]->toArray(), $rate_plan_room["rate_plan"]->toArray());
//                        }
                    }
                }
            }
            //Add Service Rate Plans
            foreach ($hotels_new as $index_hotel_new => $hotel_new) {
                foreach ($hotel_new["hotel_rooms"] as $index_room_new => $room_new) {
                    foreach ($room_new["rates_plan_room"] as $index_rate_plan_room_new => $rate_plan_room_new) {

                        $ignore_rates_id = DateRangeHotel::where('hotel_id', $hotel_new['hotel_id'])
                            ->whereNotNull('old_id_date_range')
                            ->whereYear('date_from', $hotel_year)
                            // ->whereYear('date_to', $hotel_year)
                            ->where('rate_plan_id', $rate_plan_room_new["rate_plan_id"])
                            ->pluck('old_id_date_range');

                        $rate_history = DateRangeHotel::where('hotel_id', $hotel_new['hotel_id'])
                            ->whereNotIn('id', $ignore_rates_id)
                            ->whereYear('date_from', $hotel_year)
                            // ->whereYear('date_to', $hotel_year)
                            ->where('rate_plan_id', $rate_plan_room_new["rate_plan_id"])
                            ->where('rate_plan_room_id', $rate_plan_room_new["rate_plan_room_id"])
                            ->orderBy('date_from')
                            ->get();

                        foreach ($hotels as $hotel) {
                            if ($hotel["id"] == $hotel_new["hotel_id"]) {
                                foreach ($hotel["rooms"] as $room) {
                                    if ($room["id"] == $room_new["room_id"]) {

                                        foreach ($room["rates_plan_room"] as $rate_plan_room) {
                                            if ($rate_plan_room["id"] == $rate_plan_room_new["rate_plan_room_id"]) {
                                                $divisor = 1;
                                                if ($room["room_type"]["occupation"] == 2) {
                                                    $divisor = 2;
                                                }
                                                if ($room["room_type"]["occupation"] == 3) {
                                                    $divisor = 3;
                                                }

                                                if ($rate_history->count() > 0) {
//                                                    $rate_history = $rate_history->first();
//                                                    $rate_history_dataRooms = json_decode($rate_history->dataRooms, true);

                                                    $calendar_new = [
                                                        "date_from" => "",
                                                        "date_to" => "",
                                                        "price" => "",
                                                        "price_adult" => "",
                                                        "price_child" => "",
                                                        "policy_name" => ""
                                                    ];
                                                    $calendars_new = [];
                                                    foreach ($rate_history as $_dataRoom) {

                                                        $price_extra = 0;
                                                        if ($_dataRoom['room_id'] == $room["id"]) {
                                                            $policy_name = PoliciesRates::find($_dataRoom["policy_id"]);
                                                            if ($policy_name != null) {
                                                                $calendar_new["policy_name"] = $policy_name->name;
                                                            }
                                                            if ($_dataRoom['price_extra'] != '' || $_dataRoom['price_extra'] > 0) {
                                                                $price_extra = $_dataRoom['price_extra'];
                                                            }
                                                            /*
                                                            if (strpos($_dataRoom['date_from'],
                                                                    $hotel_year) !== false && strpos($_dataRoom['date_to'],
                                                                    $hotel_year) !== false) {
                                                                        */

                                                            $calendar_new["flag_migrate"] = $_dataRoom['flag_migrate'];
                                                            $calendar_new["sincerada"] = ($service_year == (date('Y') + 1) ? ($_dataRoom['flag_migrate'] !== 1 ? 1 : 0) : 0);
                                                            $calendar_new["date_from"] = Carbon::parse($_dataRoom['date_from'])->format('d/m/Y');
                                                            $calendar_new["date_to"] = Carbon::parse($_dataRoom['date_to'])->format('d/m/Y');
                                                            $calendar_new["price"] = ceil((float)($_dataRoom['price_adult']));
                                                            $calendar_new["price_adult"] = ceil(((((float)($_dataRoom['price_adult']) + (float)($price_extra)) + (((float)($_dataRoom['price_adult']) + (float)($price_extra)) * ($markup / 100))) / $divisor));
                                                            $calendar_new["price_child"] = ceil((((float)($_dataRoom['price_child'])) + (((float)($_dataRoom['price_child'])) * ($markup / 100))));
                                                            array_push($calendars_new, $calendar_new);
                                                            // }
                                                        }
                                                    }

                                                    $hotels_new[$index_hotel_new]["hotel_rooms"][$index_room_new]["rates_plan_room"][$index_rate_plan_room_new]["calendars"] = $calendars_new;
                                                }
                                            }
                                        }
                                    }
                                }

                            }//
                        }
                    }
                }
            }
//        dd($hotels_new); die;
//       dd($hotels_new[$index_hotel_new]["hotel_rooms"][$index_room_new]["rates_plan_room"][$index_rate_plan_room_new]["calendars"]);

            //llenar Arreglo de hoteles
            foreach ($hotels_new as $hotel) {
                foreach ($data["cities"] as $index_data_city => $city) {
                    if ($city["city_id"] == $hotel["hotel_city_id"]) {
                        foreach ($city["categories"] as $index_data_category => $category) {
                            if ($category["hotel_class_id"] == $hotel["hotel_type_class_id"]) {
                                array_push($data["cities"][$index_data_city]["categories"][$index_data_category]["hotels"],
                                    $hotel);
                            }
                        }
                    }
                }
            }
//        return $hotels_new;
            //End Arreglo de servicios
            //Proceso para limpiar el array
            //delete rate plan room
            foreach ($data["cities"] as $index_new_city => $city) {
                foreach ($city["categories"] as $index_new_category => $category) {
                    foreach ($category["hotels"] as $index_new_hotel => $hotel) {
                        foreach ($hotel["hotel_rooms"] as $index_new_room => $room) {
                            foreach ($room["rates_plan_room"] as $index_new_rate_plan_room => $rate_plan_room) {
                                if (count($rate_plan_room["calendars"]) == 0) {
                                    unset($data["cities"][$index_new_city]["categories"][$index_new_category]["hotels"][$index_new_hotel]["hotel_rooms"][$index_new_room]["rates_plan_room"][$index_new_rate_plan_room]);
                                }
                            }
                        }
                    }
                }
            }
            //delete rooms
            foreach ($data["cities"] as $index_new_city => $city) {
                foreach ($city["categories"] as $index_new_category => $category) {
                    foreach ($category["hotels"] as $index_new_hotel => $hotel) {
                        foreach ($hotel["hotel_rooms"] as $index_new_room => $room) {
                            if (count($room["rates_plan_room"]) == 0) {
                                unset($data["cities"][$index_new_city]["categories"][$index_new_category]["hotels"][$index_new_hotel]["hotel_rooms"][$index_new_room]);
                            }
                        }
                    }
                }
            }
            //delete hotels
            foreach ($data["cities"] as $index_new_city => $city) {
                foreach ($city["categories"] as $index_new_category => $category) {
                    foreach ($category["hotels"] as $index_new_hotel => $hotel) {
//                    foreach ($hotel["hotel_rooms"] as $index_new_room => $room) {
                        if (count($hotel["hotel_rooms"]) == 0) {
                            unset($data["cities"][$index_new_city]["categories"][$index_new_category]["hotels"][$index_new_hotel]);
                        }
//                    }
                    }
                }
            }
//        dd($data["cities"]);

            //delete categories
            foreach ($data["cities"] as $index_new_city => $city) {
                foreach ($city["categories"] as $index_new_category => $category) {
                    if (count($category["hotels"]) == 0) {
                        unset($data["cities"][$index_new_city]["categories"][$index_new_category]);
                    }
                }
            }
            //End delete categories

            //delete cities
            foreach ($data["cities"] as $index_new_city => $city) {
                if (count($city["categories"]) == 0) {
                    unset($data["cities"][$index_new_city]);
                }
            }
            //End delete cities

            Cache::put('excel_hotels', $data, 3600);

            return response()->json($data, 200);

        } catch (\Exception $e) {
            $error = $this->throwError($e);

            return response()->json($error, 404);
        }
    }

    public function serviceTranslationsExport()
    {
        return Excel::download(new  \App\Exports\ServiceTranslationsExport(), 'Servicios' . '.xlsx');
    }

    public function getNotesSupplementsHotelsExport(Request $request)
    {

        $hotels = Hotel::where('status', '=', 1)
            ->with([
                'rates_plans' => function ($query) {
                    $query->select(['id', 'code', 'name', 'status', 'hotel_id']);
                    $query->where('status', 1);
                    $query->with([
                        'translations_notes' => function ($query) {
                            $query->select(['id', 'value', 'object_id', 'language_id']);
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->get(['id', 'name']);

        $hotels = $hotels->transform(function ($query) {
            $query->rates_plans->transform(function ($query) {
                if ($query->translations_notes->count() > 0) {
                    $query->translations_notes_es = $query->translations_notes->where('language_id', 1)->first()->value;
                } else {
                    $query->translations_notes_es = '';
                }
                unset($query->translations_notes);
                return $query;
            });
            return $query;
        });


        return Excel::download(new  \App\Exports\NotesRatesHotelsExport($hotels),
            'notes_rates_hotels.xlsx');

    }

    public function clientsListExport(Request $request)
    {
        $clients = Client::where('status', '=', 1)
            ->with([
                'markets' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
//            ->with([
//                'countries' => function ($query) {
//                    $query->select(['id', 'iso']);
//                    $query->with([
//                        'translations' => function ($query) {
//                            $query->select(['object_id', 'value']);
//                            $query->where('type', 'country');
//                            $query->where('language_id', 1);
//                        }
//                    ]);
//                }
//            ])
            ->get(['id', 'code', 'name', 'country_id', 'market_id', 'ruc']);

//        $clients = $clients->transform(function ($client) {
//            $validate_ruc = validateRuc($client->ruc)['success'];
//            $client->validate_ruc = $validate_ruc;
//            return $client;
//        });

        return Excel::download(new  \App\Exports\ClientsExport($clients),
            'clients.xlsx');

    }

    public function getRatesConfigurationError(Request $request)
    {
        $hotels = Hotel::where('status', 1)
            ->with([
                'rates_plans' => function ($query) {
                    $query->select(['id', 'code', 'name', 'hotel_id', 'status']);
                    $query->where('status', 1);
                    $query->with([
                        'associations' => function ($query) {
                            $query->select([
                                'id',
                                'rate_plan_id',
                                'entity',
                                'object_id'
                            ]);
                        }
                    ]);
                }
            ])
            ->get(['id', 'name']);

        $group = collect();

        foreach ($hotels as $hotel) {

            $error_associations = collect();
            foreach ($hotel['rates_plans'] as $rates_plan) {
                $_association = $rates_plan['associations'];
                $_association_market = $_association->where('entity', 'Market')->count();
                $_association_country = $_association->where('entity', 'Country')->count();
                $_association_client = $_association->where('entity', 'Client')->count();
                if ($_association_market > 0 and $_association_country > 0 and $_association_client > 0) {
                    $error_associations->add([
                        'id' => $rates_plan->id,
                        'name' => $rates_plan->name,
                        'association_market' => $_association_market,
                        'association_country' => $_association_country,
                        'association_client' => $_association_client,
                    ]);
                }

                if ($_association_market > 0 and $_association_country > 0 and $_association_client == 0) {
                    $error_associations->add([
                        'id' => $rates_plan->id,
                        'name' => $rates_plan->name,
                        'association_market' => $_association_market,
                        'association_country' => $_association_country,
                        'association_client' => $_association_client,
                    ]);
                }

                if ($_association_market > 0 and $_association_client > 0 and $_association_country == 0) {
                    $error_associations->add([
                        'id' => $rates_plan->id,
                        'name' => $rates_plan->name,
                        'association_market' => $_association_market,
                        'association_country' => $_association_country,
                        'association_client' => $_association_client,
                    ]);
                }

                if ($_association_market == 0 and $_association_client > 0 and $_association_country > 0) {
                    $error_associations->add([
                        'id' => $rates_plan->id,
                        'name' => $rates_plan->name,
                        'association_market' => $_association_market,
                        'association_country' => $_association_country,
                        'association_client' => $_association_client,
                    ]);
                }

                if ($_association_market == 0 and $_association_client == 0 and $_association_country > 0) {
                    $error_associations->add([
                        'id' => $rates_plan->id,
                        'name' => $rates_plan->name,
                        'association_market' => $_association_market,
                        'association_country' => $_association_country,
                        'association_client' => $_association_client,
                    ]);
                }

                if ($_association_market == 0 and $_association_client > 0 and $_association_country == 0) {
                    $error_associations->add([
                        'id' => $rates_plan->id,
                        'name' => $rates_plan->name,
                        'association_market' => $_association_market,
                        'association_country' => $_association_country,
                        'association_client' => $_association_client,
                    ]);
                }
            }

            if ($error_associations->count() > 0) {
                $group->add([
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'rate_plans' => $error_associations
                ]);
            }

        }

        return Excel::download(new  \App\Exports\HotelRatesAssociationsErrorExport($group),
            'hotels_rates_associations_rates.xlsx');

    }


    public function translationConfigMarkupsExport(Request $request, $type, $id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $config_markup = ConfigMarkup::where('id', '=', $id)->first();
            $field = ($type == 'service') ? 'item_rate_id' : 'item_rate_plan_id';

            if ($config_markup) {
                $logs = CloneLog::with(['service', 'hotel'])
                    ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime($config_markup->created_at)))
                    ->where('created_at', '<=', date("Y-m-d H:i:s", strtotime($config_markup->updated_at)));

                if ($config_markup->category_id > 0) {
                    $logs = $logs->where('category_id', $config_markup->category_id);
                }

                $logs = $logs->where('type', '=', $type)
                    ->where('status', '=', 1)
                    ->groupBy($field)
                    ->get()->toArray();

                return Excel::download(new \App\Exports\ConfigMarkupsExport($type, $logs),
                    'ProteccionMarkups' . '.xlsx');
            } else {
                return response()->json([
                    'success' => 'false',
                    'config_markup' => $config_markup
                ]);
            }
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }


    public function translationConfigMarkupsHotelesExport(Request $request, $type, $category_id)
    {
        // $type = $request->__get('type'); $category_id = $request->__get('category_id');

        // $logs = CloneLog::with(['service', 'hotel'])
        //     ->where('category_id', $category_id)
        //     ->where('type', '=', $type)
        //     ->where('status', '=', 1)
        //     ->get()->toArray();


        $data = Hotel::with([
            'rates_plans' => function ($query) {
                $query->where('flag_process_markup', 1);
                $query->where('status', 1);
            },
            'channels'
        ])->whereHas('rates_plans', function ($query) {
            $query->where('flag_process_markup', 1);
            $query->where('status', 1);
        })->where('typeclass_id', $category_id)->where('status', 1)->get()->toArray();

        // dd($data);

        // dd($category_id);
        // dd($logs[0]);

        return Excel::download(new \App\Exports\ConfigMarkupHotelsExport($data), 'ProteccionMarkups' . '.xlsx');
    }


    public function reservationClientsExport(Request $request)
    {
        $reservation = Reservation::with([
            'client' => function ($query) {
                $query->select(['id', 'name', 'code', 'ruc']);
            }
        ])->with([
            'executive' => function ($query) {
                $query->select(['id', 'code', 'name']);
            }
        ])->whereDate('created_at', '>=', '2022-01-01')
            ->whereDate('created_at', '<=', Carbon::now()->format('Y-m-d'))
            ->get(['id', 'client_id', 'file_code', 'executive_id', 'created_at']);

        $clients_ids = $reservation->pluck('client_id')->unique()->values();


        $clients = Client::where('status', '=', 1)
            ->with([
                'markets' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
            ->with([
                'countries' => function ($query) {
                    $query->select(['id', 'iso']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select(['object_id', 'value']);
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])
            ->whereIn('id', $clients_ids)
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'country_id', 'market_id', 'ruc']);


        $clients = $clients->transform(function ($client) {
            $validate_ruc = validateRuc($client->ruc)['success'];
            $client->validate_ruc = $validate_ruc;
            return $client;
        });

        $clients = $clients->where('validate_ruc', false)->values();

        return Excel::download(new  \App\Exports\ClientsExport($clients),
            'reservas.xlsx');

    }

    public function getRatesStelaExport(Request $request)
    {
        $year_to = (int)date("Y") + 1;
        $date_range_hotels = DateRangeHotel::with([
            'rate_plan_room.room.hotel.channels'
        ])
            ->where('date_from', 'LIKE', "%" . $year_to . "-%")
            ->where(function ($query) {
                $query->orWhere('flag_migrate', '=', 1);
                $query->orWhereNull('flag_migrate');
            })
            // ->take(10)
            ->groupBy('rate_plan_id')
            ->get()->toArray();

        $date_range_services = ServiceRatePlan::with([
            'service_rate.service'
        ])
            ->where('date_from', 'LIKE', "%" . $year_to . "-%")
            ->where(function ($query) {
                $query->orWhere('flag_migrate', '=', 1);
                $query->orWhereNull('flag_migrate');
            })
            ->whereNull('deleted_at')
            // ->take(10)
            ->groupBy('service_rate_id')
            ->orderBy('created_at', 'desc')->get()->toArray();

        $data = [
            'date_range_hotels' => $date_range_hotels,
            'date_range_services' => $date_range_services,
        ];

        return Excel::download(new  \App\Exports\RatesStelaExport($data),
            'rates_stela_export.xlsx');
    }

    public function getHotelsNotes(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $hotels = Hotel::on('mysql_read')->select('id', 'name')
                ->with([
                    'translations' => function ($query) {
                        $query->where('slug', '=', 'summary');
                    }
                ])
                ->whereHas('translations', function ($query) {
                    $query->where('slug', '=', 'summary');
                })
                ->where('status', '=', 1)
                // ->where('id', '!=', 376)
                ->get()->toArray();

            // print_r($hotels); die;

            $data = [
                'hotels' => $hotels,
            ];

            Excel::store(new \App\Exports\HotelNotesExport($data),
                'hotels_notes.xlsx', 'public');

            return redirect()->to('/storage/hotels_notes.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getHotelsDescriptions(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $hotels = Hotel::on('mysql_read')->select('id', 'name')
                ->with([
                    'translations' => function ($query) {
                        $query->where('type', '=', 'hotel');
                        $query->where('slug', '=', 'hotel_description');
                    }
                ])
                ->whereHas('translations', function ($query) {
                    $query->where('type', '=', 'hotel');
                    $query->where('slug', '=', 'hotel_description');
                })
                ->where('status', '=', 1)
                // ->where('id', '!=', 376)
                ->get()->toArray();

            // print_r($hotels); die;

            $data = [
                'hotels' => $hotels,
            ];

            Excel::store(new \App\Exports\HotelDescriptionsExport($data),
                'hotels_descriptions.xlsx', 'public');

            return redirect()->to('/storage/hotels_descriptions.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getRoomsNotes(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $rooms = Room::on('mysql_read')->select(['id', 'hotel_id'])->with([
                'hotel' => function ($query) {
                    $query->select('id', 'name', 'status');
                    $query->where('status', '=', 1);
                },
                'translations' => function ($query) {
                    $query->orWhere('slug', '=', 'room_description');
                    $query->orWhere('slug', '=', 'room_name');
                }
            ])
                ->whereHas('translations', function ($query) {
                    $query->orWhere('slug', '=', 'room_description');
                    $query->orWhere('slug', '=', 'room_name');
                })
                ->whereHas('hotel', function ($query) {
                    $query->where('status', '=', 1);
                })
                ->where('state', '=', 1)
                ->get();

            $rooms = $rooms->each(function ($room, $r) {
                $room->name = collect([]);
                $room->description = collect([]);

                $room->translations->each(function ($translate, $t) use ($room) {

                    if ($translate->slug == 'room_name') {
                        $room->name->push($translate->value);
                    }

                    if ($translate->slug == 'room_description') {
                        $room->description->push($translate->value);
                    }

                    return $translate;
                });

                $room->translations = [];
                return $room;
            });

            $rooms = $rooms->toArray();
            // dd($rooms);

            $data = [
                'rooms' => $rooms,
            ];

            Excel::store(new \App\Exports\RoomDescriptionsExport($data),
                'rooms_descriptions.xlsx', 'public');

            return redirect()->to('/storage/rooms_descriptions.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getServicesNotes(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $services = Service::on('mysql_read')->with(['service_translations'])
                ->with([
                    'serviceOrigin.city.translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'city');
                        $query->where('language_id', 1);
                    }
                ])
                ->with([
                    'serviceDestination.city.translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'city');
                        $query->where('language_id', 1);
                    }
                ])
                ->whereHas('service_translations')
                ->where('status', '=', 1)
                ->whereIn('id', [
                    2367,
                    1293,
                    1745,
                    1932,
                    1465,
                    2035,
                    2182,
                    1498,
                    1753,
                    2171,
                    2184,
                    2185,
                    2178,
                    1812,
                    2327,
                    2186,
                    2180,
                    1904,
                    2272,
                    2569,
                    1459,
                    2358,
                    2329,
                    1523,
                    1207,
                    2220,
                    1997,
                    2321,
                    2117,
                    1324,
                    1512,
                    2177,
                    1289,
                    2008,
                    2149,
                    2169,
                    1882,
                    2000,
                    2176,
                    2158,
                    2183,
                    2187,
                    2518,
                    3272,
                    2517,
                    2271,
                    2254,
                    2256,
                    1579,
                    2308,
                    2298,
                    2170,
                    2128,
                    2351,
                    2317,
                    1956,
                    1291,
                    2319,
                    2006,
                    1853,
                    3295,
                    3011,
                    1734,
                    2320,
                    2205,
                    2204,
                    2207,
                    1590,
                    1223,
                    2224,
                    2354,
                    2808,
                    2597,
                    2359,
                    1810,
                    1595,
                    1460,
                    2410,
                    1773,
                    1647,
                    3312,
                    1282,
                    2360,
                    1449,
                    1910,
                    2353,
                    2890,
                    1492,
                    2366,
                    2363,
                    1793,
                    1883,
                    2118,
                    2303,
                    1632,
                    2681,
                    2632,
                    3294,
                    1575,
                    2456,
                    1443,
                    2348,
                    1979,
                    1854,
                    1959,
                    1961,
                    3273,
                    1866,
                    1960,
                    1963,
                    1438,
                    1497,
                    2203,
                    1292,
                    2570,
                    2362,
                    1799,
                    1984,
                    1986,
                    1318,
                    1767,
                    1772,
                    1658,
                    2137,
                    1890,
                    1795,
                    2213,
                    2325,
                    1790,
                    1656,
                    2395,
                    1880,
                    2141,
                    2001,
                    1663,
                    2309,
                    2167,
                    2142,
                    2144,
                    2699,
                    1429,
                    2846,
                    1803,
                    1299,
                    2007,
                    1881,
                    1490,
                    2311,
                    2235,
                    1876,
                    1874,
                    1417,
                    1785,
                    2179,
                    1537,
                    2515,
                    2884,
                    1868,
                    3350,
                    3351,
                    1589,
                    2146,
                    1386,
                    2627,
                    2136,
                    2110,
                    1729,
                    1974,
                    1312,
                    1873,
                    2161,
                    2162,
                    2153,
                    2151,
                    1889,
                    1696,
                    3217,
                    3221,
                    1547,
                    2260,
                    2493,
                    2601,
                    1875,
                    2595,
                    1809,
                    2131,
                    2115,
                    2193,
                    2809,
                    2845,
                    2194,
                    2148,
                    1491,
                    2154,
                    2156,
                    1549,
                    1814,
                    1359,
                    1999,
                    2221,
                    1728,
                    2527,
                    3109,
                    3210,
                    2847,
                    2516,
                    1566,
                    2172,
                    2222,
                    2973,
                    2016,
                    1646,
                    2526,
                    1976,
                    1587
                ])
//                ->skip(0)->take(500)
                ->get()->toArray();

            $n = 0;
            foreach ($services as $s) {
                $services[$n]['total_sales'] = ReservationsService::where('service_id', $s['id'])->count();
                $n++;
            }

            $data = [
                'services' => $services,
            ];

//             return Response::json($services);

            return (new ServiceNotesExport($data))->download('services_notes.xlsx');

            // Excel::store(new \App\Exports\ServiceNotesExport($data),
            //     'services_notes.xlsx', 'public');

            // return redirect()->to('/storage/services_notes.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getServicesItineraries(Request $request, $year = '')
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $year = (!empty($year)) ? ($year . '-01-01') : date("Y-m-d");
            // --
            $year_to = date("Y", strtotime($year));
            $year_from = date("Y", strtotime("-1 year", strtotime($year)));

            $date_to = $year_to . '-09-30';
            $date_from = $year_from . '-10-01';

            $services = Service::on('mysql_read')->with(['service_translations'])
                ->whereHas('service_translations')
                ->where('status', '=', 1)
                ->whereHas('reservations_services', function ($query) use ($date_from, $date_to) {
                    $query->select('id', 'service_id', 'created_at', 'status');
                    $query->where('status', '=', 1);
                    $query->whereBetween('created_at', [$date_from, $date_to]);
                })
                ->withCount([
                    'reservations_services' => function ($query) use ($date_from, $date_to) {
                        // $query->select('service_id', 'created_at', 'status');
                        $query->where('status', '=', 1);
                        $query->whereBetween('created_at', [$date_from, $date_to]);
                    }
                ]);

            $services = $services->get()->toArray();

            $data = [
                'services' => $services,
                'year_from' => $year_from,
                'year_to' => $year_to,
            ];

            Excel::store(new \App\Exports\ServiceItinerariesExport($data),
                'services_itineraries.xlsx', 'public');

            return redirect()->to('/storage/services_itineraries.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function getHotelsPreferredNotes(Request $request)
    {
        try {
            $file = $request->file;
            $array = (new ImportablesImport)->toArray($file);
            $array = $array[0];
            $headers = $array[0];
            $data = array_slice($array, 1);
            $names = [];

            foreach ($data as $key => $value) {
                $names[] = trim($value[0]);
            }

            $hotels = Hotel::on('mysql_read')->with([
                'translations' => function ($query) {
                    $query->where('slug', '=', 'summary');
                }
            ])
                ->whereIn('name', $names)
                ->where('status', '=', 1)
                ->get()->toArray();

            $data = [
                'hotels' => $hotels,
            ];

            $filename = Excel::store(new \App\Exports\HotelPreferredNotesExport($data),
                'hotels_preferred_notes.xlsx', 'public');

            return response()->json([
                'type' => 'success',
                'hotels' => $hotels,
                'count_names' => count($names),
                'count_hotels' => count($hotels),
                'names' => $names,
                'file' => config('app.url') . '/storage/hotels_preferred_notes.xlsx',
                'filename' => $filename,
            ]);

        } catch (\Exception $ex) {
            dd($this->throwError($ex));
        }
    }

    public function services_mapi(Request $request)
    {
        try {
            $services = Service::select(['id', 'name', 'aurora_code', 'status'])
                ->where('aurora_code', 'like', '%MPIM%')
                ->with([
                    'service_translations' => function ($query) {
                        $query->select([
                            'name',
                            'name_commercial',
                            'description',
                            'description_commercial',
                            'itinerary',
                            'itinerary_commercial',
                            'service_id',
                            'language_id'
                        ]);
                        $query->with('language');
                    }
                ])
                ->where('status', '=', 1)->get()->toArray();

            $data = [
                'services' => $services,
            ];

            return Excel::download(new  \App\Exports\ServicesMapiExport($data),
                'services_mapi.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function clientsBrasilExport(Request $request)
    {
        try {
            $clients = Client::with(['markets'])->where('country_id', '=', 12)->get();

            return Excel::download(new  \App\Exports\ClientsExport($clients),
                'clients-br.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function export_surveys(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $fecini = $request->__get('fecini'); // FORMAT (Y-m-d)
            $fecout = $request->__get('fecout'); // FORMAT (Y-m-d)

            $data = [
                'fecini' => date("d/m/Y", strtotime($fecini)),
                'fecout' => date("d/m/Y", strtotime($fecout))
            ];

            $stellaService = new StellaService();
            $surveys = $this->toArray($stellaService->search_surveys($data));

            $files = [];

            $surveys = array_map(function ($survey) use (&$files) {
                $files[] = $survey['nroref'];
                $_survey = [];

                $descri = $survey['descri'];
                $descri = preg_replace('/[\x00-\x1F\x7F]/u', '', $descri);
                $descri = html_entity_decode($descri);
                $descri = htmlentities($descri, ENT_QUOTES | ENT_IGNORE, "UTF-8");

                $name = $survey['passenger_name'];
                $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
                $name = html_entity_decode($name);
                $name = htmlentities($name, ENT_QUOTES | ENT_IGNORE, "UTF-8");

                $encoding = mb_detect_encoding($descri, 'UTF-8', true);

                if ($encoding) {
                    $_survey['descri'] = trim($descri);
                    $_survey['nroref'] = trim($survey['nroref']);
                    $_survey['passenger_name'] = trim($name);
                    $_survey['passenger_nacion'] = trim($survey['passenger_nacion']);

                    return $_survey;
                }
            }, $surveys);

            // return view('exports.surveys')->with('surveys', $surveys)->with('packages', []);

            $reservations = Reservation::on('mysql_read')
                ->select('id', 'file_code', 'object_id', 'entity')
                ->whereIn('file_code', $files)->get();
            $packages = [];

            $reservations->each(function ($reservation) use (&$packages) {
                $package_id = 0;

                if ($reservation->object_id > 0) {
                    if ($reservation->entity == 'Quote') {
                        $log = QuoteLog::on('mysql_read')
                            ->where('quote_id', '=', $reservation->object_id)
                            ->where('type', '=', 'from_package')->first();

                        if ($log) {
                            $package_id = $log->object_id;
                        }
                    }

                    if ($reservation->entity == 'Package') {
                        $package_id = $reservation->object_id;
                    }

                    if ($package_id > 0) {
                        $package = Package::on('mysql_read')->select('id')->with([
                            'translations' => function ($query) {
                                $query->select('tradename', 'package_id', 'name');
                                $query->where('language_id', '=', 1);
                            }
                        ])->where('id', '=', $package_id)->first()->toArray();
                    }
                }

                if ($package_id > 0) {
                    $packages[$reservation->file_code] = $package;
                }
            });

            /*
            return response()->json([
                'surveys' => $surveys,
                'files' => $files,
                // 'reservations' => $reservations,
                // 'packages' => $packages,
            ]);
            */

            return Excel::download(new \App\Exports\SurveysExport($surveys, $packages), 'surveys-export.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function ce_indicators(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $years = [2019, 2020, 2021, 2022, 2023, 2024];
            $markets = [1, 2, 3];
            $response = ['years' => $years, 'markets' => $markets];

            foreach ($years as $year) {
                // foreach($markets as $sector)
                // {
                // CLIENTS..
                $access_clients_a2 = Client::on('mysql_read')
                    ->whereHas('client_sellers', function ($query) use ($year) {
                        // $query->where('client_sellers.status', '=', 1);
                        $query->whereYear('client_sellers.created_at', $year);
                    })
                    /*
                    ->whereHas('markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    })
                    */
                    // ->where('clients.status', '=', 1)
                    ->count();

                $response['access_clients_a2'][$year] = $access_clients_a2;

                $user_access_clients_a2 = ClientSeller::on('mysql_read')
                    // ->where('status', '=', 1)
                    ->whereYear('client_sellers.created_at', $year)
                    /*
                    ->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    })
                    */
                    ->count();

                $response['user_access_clients_a2'][$year] = $user_access_clients_a2;

                $login_clients_a2 = Client::on('mysql_read')
                    ->whereHas('client_sellers', function ($query) use ($year) {
                        $query->where('users.count_login', '>', 0);
                        $query->whereYear('client_sellers.created_at', $year);
                    })
                    /*
                    ->whereHas('markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    })
                    */
                    //->where('clients.status', '=', 1)
                    ->count();

                $response['login_clients_a2'][$year] = $login_clients_a2;

                $user_login_clients_a2 = ClientSeller::on('mysql_read')
                    ->whereHas('user', function ($query) {
                        $query->where('users.count_login', '>', 0);
                    })
                    /*
                    ->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    })
                    */
                    // ->where('status', '=', 1)
                    ->whereYear('client_sellers.created_at', $year)
                    ->count();

                $response['user_login_clients_a2'][$year] = $user_login_clients_a2;

                $reservation_clients_a2 = Reservation::on('mysql_read')
                    ->where('status', '=', 1)
                    ->whereYear('created_at', $year)
                    /*
                    ->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    })
                    */
                    ->where('reservator_type', '=', 'client')->count();

                $response['reservation_clients_a2'][$year] = $reservation_clients_a2;

                $reservation_ope_clients_a2 = Reservation::on('mysql_read')
                    ->where('status', '=', 1)
                    ->whereYear('date_init', $year)
                    /*
                    ->whereHas('client.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    })
                    */
                    ->where('reservator_type', '=', 'client')->count();

                $response['reservation_ope_clients_a2'][$year] = $reservation_ope_clients_a2;

                $quote_clients_a2 = Quote::on('mysql_read')
                    ->where('status', '=', 1)
                    ->whereYear('created_at', $year)
                    ->whereHas('user', function ($query) {
                        $query->where('user_type_id', '=', 4);
                    })
                    ->count();
                /*
                ->where(function ($query) use ($sector) {
                    $query->orWhereHas('user.markets', function ($query) use ($sector) {
                        $query->where('code', 'like', $sector . '%');
                    });
                    $query->orWhereHas('user', function ($query) use ($sector) {
                        $query->where('grupo_code', 'like', $sector . '%');
                    });
                })
                */

                $response['quote_clients_a2'][$year] = $quote_clients_a2;

                // EXECUTIVES..
                $reservation_executives_a2 = Reservation::on('mysql_read')
                    ->where('status', '=', 1)
                    ->whereYear('created_at', $year)
                    /*
                    ->where(function ($query) use ($sector) {
                        $query->orWhereHas('create_user.markets', function ($query) use ($sector) {
                            $query->where('code', 'like', $sector . '%');
                        });
                        $query->orWhereHas('create_user', function ($query) use ($sector) {
                            $query->where('grupo_code', 'like', $sector . '%');
                        });
                    })
                    */
                    ->whereIn('reservator_type', ['excecutive', 'executive'])->count();

                $response['reservation_executives_a2'][$year] = $reservation_executives_a2;

                $reservation_ope_executives_a2 = Reservation::on('mysql_read')
                    ->where('status', '=', 1)
                    ->whereYear('date_init', $year)
                    /*
                    ->where(function ($query) use ($sector) {
                        $query->orWhereHas('create_user.markets', function ($query) use ($sector) {
                            $query->where('code', 'like', $sector . '%');
                        });
                        $query->orWhereHas('create_user', function ($query) use ($sector) {
                            $query->where('grupo_code', 'like', $sector . '%');
                        });
                    })
                    */
                    ->whereIn('reservator_type', ['excecutive', 'executive'])->count();

                $response['reservation_ope_executives_a2'][$year] = $reservation_ope_executives_a2;

                $quote_executives_a2 = Quote::on('mysql_read')
                    ->where('status', '=', 1)
                    ->whereYear('created_at', $year)
                    ->whereHas('user', function ($query) {
                        $query->where('user_type_id', '=', 3);
                    })
                    /*
                    ->where(function ($query) use ($sector) {
                        $query->orWhereHas('user.markets', function ($query) use ($sector) {
                            $query->where('code', 'like', $sector . '%');
                        });
                        $query->orWhereHas('user', function ($query) use ($sector) {
                            $query->where('grupo_code', 'like', $sector . '%');
                        });
                    })*/
                    ->count();

                $response['quote_executives_a2'][$year] = $quote_executives_a2;
                // }
            }

            return Excel::download(new \App\Exports\IndicatorsExport($response), 'indicators-export.xlsx');

            return response()->json([
                'type' => 'success',
                'data' => $response,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function passenger_export(Request $request)
    {
        $total_pax = $request->input("total");
        $lang = $request->input("lang") ? $request->input("lang") : 'en';
        $countries = Country::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        $dataCountry = [];
        foreach ($countries as $country) {
            array_push($dataCountry, [
                'iso' => $country->iso,
                'country' => $country->translations[0]->value,
                'phone_code' => $country->phone_code,
            ]);
        }

        array_multisort(array_column($dataCountry, 'country'), $dataCountry);

        $doctypes = Doctype::with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        $dataDoctipes = [];
        foreach ($doctypes as $doctype) {
            array_push($dataDoctipes, [
                'iso' => $doctype->iso,
                'doctype' => $doctype->translations[0]->value
            ]);
        }

        array_multisort(array_column($dataDoctipes, 'doctype'), $dataDoctipes);

        return Excel::download(new  \App\Exports\PassengerExport($total_pax, $dataCountry, $dataDoctipes),
            'passengers.xls');
    }

    public function passenger_import(Request $request)
    {

        $import = new ReadPassengersImport;

        Excel::import($import, $request->file('file'));

        return response()->json([
            'type' => 'success',
            'data' => $import->data,
        ]);
    }


    public function storage_extension_error(Request $request)
    {
        try {
            $files = LitoExtensionFile::with(['type'])
                ->where('created_at', 'like', '2024-01-08%')->get();

            return Excel::download(new  \App\Exports\LitoFileStorageExport($files),
                'report.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

}



