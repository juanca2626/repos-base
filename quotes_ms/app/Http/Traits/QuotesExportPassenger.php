<?php

namespace App\Http\Traits;

use App\Models\Client;
use App\Models\Language;
use App\Models\Quote;
use App\Models\QuoteAgeChild;
use App\Models\QuoteCategory;
use App\Models\QuotePassenger;
use App\Models\QuotePeople;
use App\Models\QuoteService;
use App\Models\QuoteAccommodation;
use App\Models\QuoteCategoryRates;
use Carbon\Carbon;

trait QuotesExportPassenger
{
    use Quotes;

    private function geneateExportPassenger($quote_id, $quote_original_id, $quote_category_id, $client_id, $lang, $user_type_id)
    {
        $_quote_id = $quote_id;
        if (!empty($quote_original_id)) {
            $_quote_id = $quote_original_id;
        }

        $data = [
            'quote_id'            => $_quote_id,
            'quote_name'          => "",
            'client_code'         => "",
            'client_name'         => "",
            'lang'                => "",
            'categories'          => [],
            'categories_optional' => [],
            'passengers'          => [],
            'passengers_optional' => []
        ];

        $client = Client::where('id', $client_id)->first();

        $quote = Quote::where('id', $quote_id)
            ->with([
                'people' => function ($query) {
                    $query->select(['id', 'adults', 'child', 'quote_id']);
                }
            ])
            ->first();
        $ages_child = QuoteAgeChild::where('quote_id', $quote_id)->get()->toArray();

        foreach ($ages_child as $index_age => $age) {
            $ages_child[$index_age]["validate"] = 0;
        }

        $data['quote_name'] = $quote->name;
        $data['client_code'] = (isset($client->code)) ? $client->code : '';
        $data['client_name'] = (isset($client->name)) ? $client->name : '';
        $data['client_commission'] = (isset($client->commission)) ? $client->commission : '';
        $data['client_commission_status'] = (isset($client->commission_status)) ? $client->commission_status : '';
        $data['user_type_id'] = (isset($user_type_id)) ? $user_type_id : '';
        $data['lang'] = $lang;

        $language_id = Language::where('iso', $lang)->first()->id;

        $category = QuoteCategory::where('id', $quote_category_id)
            ->where('quote_id', $quote_id)
            ->with('type_class.translations')->first();

        $accommodations = QuoteAccommodation::where('quote_id', $quote_id)->get()->toArray();
        $data['accommodations'] = count($accommodations) > 0 ? $accommodations[0] : [];
        $data['passengers'] = QuotePassenger::with('age_child')->where('quote_id', $quote_id)->get()->toArray();
        $data['passengers'] = $this->checkTypePassengers($data['passengers'], $quote->people->first());
        $included_child = 0;
        $count_adults = 1;
        $count_child = 1;
        foreach ($data['passengers'] as $index_passenger => $passenger) {
            if ($passenger["type"] == "ADL") {
                $data['passengers'][$index_passenger]["index"] = $count_adults;
                $count_adults++;
            }
            $data['passengers'][$index_passenger]["total"] = 0;
            $data['passengers'][$index_passenger]["total_optional"] = 0;


            if ($passenger["type"] == "CHD") {


                $data['passengers'][$index_passenger]["index"] = $count_child;
                $count_child++;

                $data['passengers'][$index_passenger]["age"] = $data['passengers'][$index_passenger]['age_child']['age'] ?? 0;
            }
        }
        $data["passengers_optional"] = $data["passengers"];

        array_push($data["categories"], [
            'category' => $category['type_class']["translations"][0]['value'],
            'services' => []
        ]);
        array_push($data["categories_optional"], [
            'category' => $category['type_class']["translations"][0]['value'],
            'services' => []
        ]);

        $quote_services = QuoteService::where('quote_category_id', $category["id"])
            ->where('optional', 0)
            ->with('amount')
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with('children_ages');
                }
            ])->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('slug', 'room_description');
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.room.room_type.type_room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])->with([
                'service_rooms_hyperguest.rate_plan.meal.translations' => function ($query) use (
                    $language_id
                ) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms_hyperguest.room.room_type',
                'service_rooms_hyperguest.room.translations' => function ($query) use (
                    $language_id
                ) {
                    $query->where('language_id', $language_id);
                },
            ])
            ->with('hotel.channel')
            ->with('service_rate')
            ->with([
                'service_rate.service_rate_data' => function ($query) {
                    $query->select('id', 'price_dynamic');
                }
            ])
            ->with([
                'passengers' => function ($query) {
                    $query->with('passenger.age_child');
                    $query->orderBy('quote_passenger_id');
                }
            ])
            ->orderBy('date_in')->get()->toArray();

        $quote_services_optional = QuoteService::where('quote_category_id', $category["id"])
            ->where('optional', 1)
            ->with('amount')
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with('children_ages');
                }
            ])->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('slug', 'room_description');
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.room.room_type.type_room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])->with([
                'service_rooms_hyperguest.rate_plan.meal.translations' => function ($query) use (
                    $language_id
                ) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms_hyperguest.room.room_type',
                'service_rooms_hyperguest.room.translations' => function ($query) use (
                    $language_id
                ) {
                    $query->where('language_id', $language_id);
                },
            ])
            ->with('hotel.channel')
            ->with('service_rate')
            ->with([
                'service_rate.service_rate_data' => function ($query) {
                    $query->select('id', 'price_dynamic');
                }
            ])
            ->with([
                'passengers' => function ($query) {
                    $query->with('passenger.age_child');
                    $query->orderBy('quote_passenger_id');
                }
            ])
            ->orderBy('date_in')
            ->orderBy('order')
            ->get()
            ->toArray();

        $quote_people_initial = QuotePeople::where('quote_id', $quote_id)->first();

        $quantity_adults = $quote_people_initial->adults;
        $quantity_child = $quote_people_initial->child;

        $total_pintar = [];


        foreach ($quote_services as $index => $quote_service) {
            if ($quote_service["type"] == "service") {
                $service = $this->calculatePriceService($quote_service, $data["passengers"]);
                $price_dynamic = $quote_service['service_rate']['service_rate_data']['price_dynamic'] ?? 0;
                $service['price_dynamic'] = $price_dynamic;
                $quote_services[$index] = $service;
                foreach ($service["passengers"] as $index_passenger_service => $passenger) {
                    $amount = isset($service["passengers"][$index_passenger_service]["amount"]) ? $service["passengers"][$index_passenger_service]["amount"] : 0;
                    $data['passengers'][$index_passenger_service]["total"] += $amount;
                    $data['passengers'][$index_passenger_service]["price_dynamic"] = $price_dynamic;
                    array_push($total_pintar, $amount);
                }
            }
            if ($quote_service["type"] == "hotel") {
                $total_accommodations = (int)$quote_service['single'] + (int)$quote_service['double'] + (int)$quote_service['triple'] + (int)$quote_service['double_child'] + (int)$quote_service['triple_child'];
                if ($total_accommodations > 0) {
                    $service = $this->calculatePriceHotelRoom($quote_service, $data["passengers"]);
                    $quote_services[$index] = $service;

                    foreach ($service["amount"] as $amount) {
                        foreach ($amount["passengers"] as $index_passenger_hotel => $passenger) {

                            $data['passengers'][$index_passenger_hotel]["total"] += $passenger["amount"];
                        }
                    }
                }
            }
        }

        $data["categories"][0]["services"] = $quote_services;

        foreach ($quote_services_optional as $index => $quote_service_optional) {
            if ($quote_service_optional["type"] == "service") {
                $service = $this->calculatePriceService($quote_service_optional, $data["passengers"]);
                $quote_services_optional[$index] = $service;

                foreach ($service["passengers"] as $index_passenger_service => $passenger) {
                    $amount = isset($service["passengers"][$index_passenger_service]["amount"]) ? $service["passengers"][$index_passenger_service]["amount"] : 0;
                    $data['passengers'][$index_passenger_service]["total_optional"] += $amount;
                }
            }
            if ($quote_service_optional["type"] == "hotel") {
                $total_accommodations = (int)$quote_service_optional['single'] + (int)$quote_service_optional['double'] + (int)$quote_service_optional['triple'] + (int)$quote_service_optional['double_child'] + (int)$quote_service_optional['triple_child'];
                if ($total_accommodations > 0) {
                    $service = $this->calculatePriceHotelRoom($quote_service_optional, $data["passengers"]);
                    $quote_services_optional[$index] = $service;

                    foreach ($service["amount"] as $amount) {
                        foreach ($amount["passengers"] as $index_passenger_hotel => $passenger) {

                            $data['passengers'][$index_passenger_hotel]["total_optional"] += $passenger["amount"];
                        }
                    }
                }
            }
        }
        $data["categories_optional"][0]["services"] = $quote_services_optional;

        // echo json_encode($this->transformSimple($data));
        // die('');
        // dd($data);
        // dd($this->orderQuote($data));


        // dd($this->validateTypeReport($data,$quantity_adults,$quantity_child));


        // Si la cotizacion tiene niños, se usar el formato detallado y si la cotizacion tiene entre sus items de hoteles, distintas catergorias de hotel, o distintos tarifarios se usar el fomato detallado
        if ($quantity_child > 0 or $this->validateTypeReport($data, $quantity_adults, $quantity_child)) {

            QuoteCategoryRates::where('quote_category_id', $quote_category_id)->delete();
            if (isset($data["accommodations"])) {

                $this->saveQuoteCategoryBasePrice($quote_category_id, $data["accommodations"], $data["categories"][0], 0, $quantity_child);
                $this->saveQuoteCategoryBasePrice($quote_category_id, $data["accommodations"], $data["categories_optional"][0], 1, $quantity_child);
            }

            return ['data' => $this->orderQuote($data), 'pax' => $quantity_adults, 'view' => 'exports.passengers'];
        } else {
            // aqui solo entrara si la cotizacion tiene:
            // Hoteles:
            // La suma de los pax de todos las habitaciones del hotel deben de ser igual al total de pax de la cotizacion
            // todos los tipos de habitacion deben ser iguales (Standar, Superior, Suite)
            // todas las tarifas deben de ser iguales
            // Debe de tener la misma cantidad de tipo de habitacions Single, Double, Triple
            $transform = $this->transformSimple($data);

            QuoteCategoryRates::where('quote_category_id', $quote_category_id)->delete();
            if (isset($transform["accommodations"])) {

                $this->saveQuoteCategoryPrice($quote_category_id, $transform["accommodations"], $transform["categories"][0], 0);
                $this->saveQuoteCategoryPrice($quote_category_id, $transform["accommodations"], $transform["categories_optional"][0], 1);
            }

            return ['data' => $this->orderQuote($transform), 'pax' => $quantity_adults, 'view' => 'exports.passengers_minimum'];
        }
    }

    public function saveQuoteCategoryPrice($quote_category_id, $accommodations, $categoria_servicio, $optional = 0)
    {


        if (isset($accommodations["single"]) and $accommodations["single"] > 0) {

            QuoteCategoryRates::create([
                'quote_category_id' => $quote_category_id,
                'type'              => 'SGL',
                'optional'          => $optional,
                'total_price'       => $categoria_servicio['services_total']["single"]
            ]);
        }

        if (isset($accommodations["double"]) and $accommodations["double"] > 0) {
            QuoteCategoryRates::create([
                'quote_category_id' => $quote_category_id,
                'type'              => 'DBL',
                'optional'          => $optional,
                'total_price'       => $categoria_servicio['services_total']['double']
            ]);
        }

        if (isset($accommodations["triple"]) and $accommodations["triple"] > 0) {
            QuoteCategoryRates::create([
                'quote_category_id' => $quote_category_id,
                'type'              => 'TPL',
                'optional'          => $optional,
                'total_price'       => $categoria_servicio['services_total']['triple']
            ]);
        }
    }

    public function saveQuoteCategoryBasePrice($quote_category_id, $accommodations, $categoria_servicio, $optional = 0, $quantity_child)
    {


        if (isset($accommodations["single"]) and $accommodations["single"] > 0) {

            QuoteCategoryRates::create([
                'quote_category_id' => $quote_category_id,
                'type'              => 'SGL',
                'optional'          => $optional,
                'total_price'       => 0
            ]);
        }

        if (isset($accommodations["double"]) and $accommodations["double"] > 0) {
            QuoteCategoryRates::create([
                'quote_category_id' => $quote_category_id,
                'type'              => 'DBL',
                'optional'          => $optional,
                'total_price'       => 0
            ]);
        }

        if (isset($accommodations["triple"]) and $accommodations["triple"] > 0) {
            QuoteCategoryRates::create([
                'quote_category_id' => $quote_category_id,
                'type'              => 'TPL',
                'optional'          => $optional,
                'total_price'       => 0
            ]);
        }

        if ($quantity_child > 0) {

            for ($i = 0; $i < $quantity_child; $i++) {
                QuoteCategoryRates::create([
                    'quote_category_id' => $quote_category_id,
                    'type'              => 'CHL ' . ($i + 1),
                    'optional'          => $optional,
                    'total_price'       => 0
                ]);
            }
        }
    }

    public function orderQuote($data)
    {
        foreach ($data["categories"] as $index => $category) {
            $data["categories"][$index]["services"] = $this->orderItems($category);
        }

        foreach ($data["categories_optional"] as $index => $category) {
            $data["categories_optional"][$index]["services"] = $this->orderItems($category);
        }

        foreach ($data["categories"] as $index => &$category) {
            foreach ($category["services"] as &$item) {
                if (isset($item['amount'][0]['passengers']) && is_array($item['amount'][0]['passengers'])) {
                    usort($item['amount'][0]['passengers'], function ($a, $b) {
                        $typeA = strtoupper($a['type'] ?? 'CHD');
                        $typeB = strtoupper($b['type'] ?? 'CHD');

                        if ($typeA === $typeB) {
                            return 0;
                        }

                        return $typeA === 'ADL' ? -1 : 1;
                    });
                }
            }
        }
        unset($category);

        return $data;
    }

    public function orderItems($category)
    {
        $quote_services_edit = [];
        $quote_services = collect($category["services"])->groupBy('date_in_format');


        foreach ($quote_services as $date => $quoteServices) {

            foreach ($quoteServices as $service) {

                if ($service['type'] == "hotel") {

                    $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];

                    if ($total_accommodations > 0) {

                        foreach ($service["amount"] as $amount) {
                            $serviceCopy = $service;
                            $amount['date_in'] = Carbon::createFromFormat('d/m/Y', $amount["date_service"])->format('Y-m-d');
                            $serviceCopy["amount"] = [];
                            $serviceCopy["amount"][0] = $amount;
                            $serviceCopy['date_in_format'] = $amount["date_in"];
                            $serviceCopy['order_general'] = 1;
                            $serviceCopy['ocupation'] = $service['single'] > 0 ? 1 : ($service['double'] > 0 ? 2 : 3);
                            $quote_services_edit[$amount["date_in"]][] = $serviceCopy;
                        }
                    }
                } else {
                    $service['order_general'] = 0;
                    $service['ocupation'] = 0;
                    $quote_services_edit[$date][] = $service;
                }
            }
        }

        ksort($quote_services_edit);


        $newQuoteServices = [];
        foreach ($quote_services_edit as $date => $services) {
            $date_in = array_column($services, 'date_in_format');
            $order_general = array_column($services, 'order_general');
            $order = array_column($services, 'order');
            $ocupation = array_column($services, 'ocupation');
            array_multisort($date_in, SORT_ASC, $order_general, SORT_ASC, $ocupation, SORT_ASC, $order, SORT_ASC, $services);


            foreach ($services as $service) {
                array_push($newQuoteServices, $service);
            }
        }


        return $newQuoteServices;
    }


    public function validateTypeReport($data, $quantity_adults, $quantity_child)
    {

        $validate_error = false;
        $accommodations = $data['accommodations'];
        foreach ($data["categories"] as $index => $category) {

            $validate_error = $this->selectedTypeReport($category["services"], $accommodations, $quantity_adults, $quantity_child);
            if ($validate_error == true) {
                return $validate_error;
            }
        }

        foreach ($data["categories_optional"] as $index => $category) {

            $validate_error = $this->selectedTypeReport($category["services"], $accommodations, $quantity_adults, $quantity_child);
            if ($validate_error == true) {
                return $validate_error;
            }
        }

        return $validate_error;
    }


    public function selectedTypeReport($category_services, $accommodations, $quantity_adults, $quantity_child)
    {
        $totalPaxQuote = $quantity_adults + $quantity_child;
        $hotels = [];
        $services = [];
        $validate_error = false;
        foreach ($category_services as $date => $service) {
            if ($service['type'] == "hotel") {
                if ($service['single'] > 0 or $service['double'] > 0 or $service['triple'] > 0) {

                    if (empty($service["service_rooms"])) {
                        $validate_error = true;
                        break;
                    }

                    if (!$service['hyperguest_pull']) {
                        $service["type_room_id"] = $service["service_rooms"][0]["rate_plan_room"]["room"]["room_type"]["type_room_id"];
                        $service["rate_plan_id"] = $service["service_rooms"][0]["rate_plan_room"]["rates_plans_id"];
                        $service["occupation"] = $service["service_rooms"][0]["rate_plan_room"]["room"]["room_type"]["occupation"];
                    } else {
                        $service["type_room_id"] = $service["service_rooms_hyperguest"][0]["room"]["room_type"]["type_room_id"];
                        $service["rate_plan_id"] = $service["service_rooms_hyperguest"][0]["rate_plan_id"];
                        $service["occupation"] = $service["service_rooms_hyperguest"][0]["room"]["room_type"]["occupation"];
                    }

                    $hotels[$service['object_id'] . "-" . $service['date_in'] . "-" . $service['nights']][] = $service;
                }
            } else {
                // La suma de los pax de cada servicio debe ser igual al total de pax de la cotizacion
                if ($totalPaxQuote != ($service['adult'] + $service['child'])) {
                    $validate_error = true;
                    break;
                }
                $services[] = $service;
            }
        }

        foreach ($hotels as $grupo => $hotel_rooms) {

            // limpimos los servicios que no tienen ocupacion
            // $hotel_rooms = [];
            // foreach($rooms as $room){
            //     if($room['single']>0 or $room['double']>0 or $room['triple']>0){
            //         array_push($hotel_rooms, $room);
            //     }
            // }

            // La suma de los pax de todos las habitaciones del hotel deben de ser igual al total de pax de la cotizacion
            $totalPax = collect($hotel_rooms)->sum(function ($room) {
                return $room['adult'] + $room['child'];
            });

            if ($totalPax != $totalPaxQuote) {
                $validate_error = true;
                break;
            }

            // todos los tipos de habitacion deben ser iguales (Standar, Superior, Suite)
            $type_room_id = collect($hotel_rooms)->groupBy('type_room_id')->map->count();
            if (count($type_room_id) > 1 or $type_room_id[$hotel_rooms[0]['type_room_id']] != count($hotel_rooms)) {
                $validate_error = true;
                break;
            }

            // todas las tarifas deben de ser iguales
            $rate_plan_id = collect($hotel_rooms)->groupBy('rate_plan_id')->map->count();
            if (count($rate_plan_id) > 1 or $rate_plan_id[$hotel_rooms[0]['rate_plan_id']] != count($hotel_rooms)) {
                $validate_error = true;
                break;
            }

            // Debe de tener la misma cantidad de tipo de habitacions Single, Double, Triple
            $hotel_rooms = collect($hotel_rooms)->groupBy('occupation')->map->count();

            if ($accommodations['single'] > 0) {
                if (!isset($hotel_rooms[1]) or ($accommodations['single'] != $hotel_rooms[1])) {
                    $validate_error = true;
                    break;
                }
            }

            if ($accommodations['double'] > 0) {
                if (!isset($hotel_rooms[2]) or ($accommodations['double'] != $hotel_rooms[2])) {
                    $validate_error = true;
                    break;
                }
            }

            if ($accommodations['triple'] > 0) {
                if (!isset($hotel_rooms[3]) or ($accommodations['triple'] != $hotel_rooms[3])) {
                    $validate_error = true;
                    break;
                }
            }
        }

        return $validate_error;
    }


    public function transformSimple($data)
    {

        $accommodations = $data['accommodations'];
        foreach ($data["categories"] as $index => $category) {

            $services = $this->transformSimpleType($category["services"], $accommodations);
            $data["categories"][$index]["services"] = $services["services"];
            $data["categories"][$index]["services_total"] = $services["totals"];
        }

        foreach ($data["categories_optional"] as $index => $category) {

            $services = $this->transformSimpleType($category["services"], $accommodations);
            $data["categories_optional"][$index]["services"] = $services["services"];
            $data["categories_optional"][$index]["services_total"] = $services["totals"];
        }

        return $data;
    }

    public function transformSimpleType($category_services, $accommodations)
    {

        $servicesReturns = [];
        $servicesTransforms = [];
        $servicesTransformTotals = [];
        $hotels = [];
        $services = [];
        foreach ($category_services as $date => $service) {
            if ($service['type'] == "hotel") {
                if ($service['single'] > 0 or $service['double'] > 0 or $service['triple'] > 0) {
                    $hotels[$service['object_id'] . "-" . $service['date_in'] . "-" . $service['nights']][$service['single'] . "-" . $service['double'] . "-" . $service['triple']] = $service;
                }
            } else {

                $service['single_import'] = 0;
                $service['double_import'] = 0;
                $service['triple_import'] = 0;

                if ($accommodations['single'] > 0) {
                    $service['single_import'] = isset($service['passengers'][0]['amount']) ? $service['passengers'][0]['amount'] : 0;
                }

                if ($accommodations['double'] > 0) {
                    $service['double_import'] = isset($service['passengers'][0]['amount']) ? $service['passengers'][0]['amount'] : 0;
                }

                if ($accommodations['triple'] > 0) {
                    $service['triple_import'] = isset($service['passengers'][0]['amount']) ? $service['passengers'][0]['amount'] : 0;
                }

                $services[] = $service;
            }
        }


        $dataHotels = [];
        foreach ($hotels as $grupo => $hotel_rooms) {
            $hotelAgrup = reset($hotel_rooms);
            foreach ($hotel_rooms as $room_type => $rooms) {

                // dd($rooms);
                $import = 0;
                foreach ($rooms['amount'] as $id => $amount) {
                    foreach ($amount['passengers'] as $passenger) {

                        if ($passenger['amount'] > 0) {
                            $import = $passenger['amount'];
                            break;
                        }
                    }

                    if ($rooms['single'] > 0) {
                        $hotelAgrup['amount'][$id]['single'] = $import;
                    }

                    if ($rooms['double'] > 0) {
                        $hotelAgrup['amount'][$id]['double'] = $import;
                    }

                    if ($rooms['triple'] > 0) {
                        $hotelAgrup['amount'][$id]['triple'] = $import;
                    }
                }
            }

            array_push($dataHotels, $hotelAgrup);
        }

        $totalSingle = 0;
        $totalDouble = 0;
        $totalTriple = 0;

        if (count($dataHotels) > 0) {
            foreach ($dataHotels as $hotels) {
                foreach ($hotels['amount'] as $amount) {
                    $totalSingle = $totalSingle + (isset($amount['single']) ? $amount['single'] : 0);
                    $totalDouble = $totalDouble + (isset($amount['double']) ? $amount['double'] : 0);
                    $totalTriple = $totalTriple + (isset($amount['triple']) ? $amount['triple'] : 0);
                }
            }
            $servicesTransforms = array_merge($servicesTransforms, $dataHotels);
        }

        if (count($services) > 0) {
            foreach ($services as $service) {

                $totalSingle = $totalSingle + (isset($service['single_import']) ? $service['single_import'] : 0);
                $totalDouble = $totalDouble + (isset($service['double_import']) ? $service['double_import'] : 0);
                $totalTriple = $totalTriple + (isset($service['triple_import']) ? $service['triple_import'] : 0);
            }
            $servicesTransforms = array_merge($servicesTransforms, $services);
        }

        $servicesTransformTotals = [
            "single" => $totalSingle,
            "double" => $totalDouble,
            "triple" => $totalTriple
        ];

        $servicesReturns["services"] = $servicesTransforms;
        $servicesReturns["totals"] = $servicesTransformTotals;

        return $servicesReturns;
    }


    private function calculatePriceHotel_antes($service, $passengers)
    {
        foreach ($passengers as $index_passenger => $passenger) {
            $passengers[$index_passenger]["amount"] = 0;
        }


        foreach ($service["amount"] as $index_amount => $amount) {
            $service["amount"][$index_amount]["passengers"] = $passengers;
        }



        $adult = $service['adult'];
        $child = $service['child'];
        $capacity = 0;
        if ($service['single'] > 0) {
            $capacity = 1;
        } elseif ($service['double'] > 0) {
            $capacity = 2;
        } else {
            $capacity = 3;
        }

        $allows_child = $service["hotel"]["allows_child"];
        $allows_teenagers = $service["hotel"]["allows_teenagers"];

        $min_age_child = $service["hotel"]["min_age_child"] ? $service["hotel"]["min_age_child"] : 0;
        $max_age_child = $service["hotel"]["max_age_child"] ? $service["hotel"]["max_age_child"] : 0;
        $min_age_teenager = $service["hotel"]["min_age_teenagers"] ? $service["hotel"]["min_age_teenagers"] : 0;
        $max_age_teenager = $service["hotel"]["max_age_teenagers"] ? $service["hotel"]["max_age_teenagers"] : 0;


        if ($child > 0) {

            $totalAdult = 0;
            foreach ($service["passengers"] as $index => $passenger) {

                if ($passenger['passenger']['type'] == "CHD") {
                    $age = $passenger['passenger']['age_child']['age'];

                    if ($allows_teenagers and ($age >= $min_age_teenager && $age <= $max_age_teenager)) {
                        $service["passengers"][$index]['type_pax'] = 'teenager';
                        $service["passengers"][$index]['type_pax_order'] = 3;
                        $service["passengers"][$index]['age'] = $age;
                    } elseif ($allows_child and ($age >= $min_age_child && $age <= $max_age_child)) {
                        $service["passengers"][$index]['type_pax'] = 'child';
                        $service["passengers"][$index]['type_pax_order'] = 2;
                        $service["passengers"][$index]['age'] = $age;
                    } else {
                        $service["passengers"][$index]['type_pax'] = 'adult';
                        $service["passengers"][$index]['type_pax_order'] = 1;
                        $service["passengers"][$index]['age'] = $age;
                        $totalAdult++;
                    }
                } else {
                    $service["passengers"][$index]['type_pax'] = 'adult';
                    $service["passengers"][$index]['type_pax_order'] = 1;
                    $service["passengers"][$index]['age'] = 28;
                    $totalAdult++;
                }
            }

            $type_pax_order = array_column($service["passengers"], 'type_pax_order');
            $age = array_column($service["passengers"], 'age');
            array_multisort($type_pax_order, SORT_ASC, $age, SORT_DESC, $service["passengers"]);
        } else {
            foreach ($service["passengers"] as $index => $passenger) {

                $service["passengers"][$index]['type_pax'] = 'adult';
                $service["passengers"][$index]['type_pax_order'] = 1;
                $service["passengers"][$index]['age'] = 28;
            }
        }

        $totalPax = count($service["passengers"]);
        if ($capacity <= $totalPax) {
            $totalAdult = $capacity;
        } else {
            $totalAdult = $totalPax;
        }



        foreach ($service["amount"] as $index_amount => $amount) { // cantidad de dias reservados

            $precioAdulto = roundLito($amount["price_per_night"] / $totalAdult);
            $totalSeleccionado = 1;
            foreach ($service["passengers"] as $index => $passenger) {
                if ($totalSeleccionado <= $totalAdult) {
                    $service["passengers"][$index]['price'] = $precioAdulto;
                } else {
                    if ($passenger['type_pax'] == "child") {
                        $service["passengers"][$index]['price'] = roundLito($amount["price_child"]);
                    } elseif ($passenger['type_pax'] == "teenager") {
                        $service["passengers"][$index]['price'] = roundLito($amount["price_teenagers"]);
                    } else {
                        $service["passengers"][$index]['price'] = -1;
                    }
                }
                $totalSeleccionado++;
            }

            foreach ($amount["passengers"] as $index_amount_passenger => $amount_passenger) {  // todos los pasajeros (5)

                foreach ($service["passengers"] as $passenger) {  // todos los pasajeros asignados al quote_service SGL, DBL, TPL  ejemplos (1 adulto) = 1 fila, (2 adultos) 2 filas, (1 adulto + 2 niños)3 filas

                    if ($amount_passenger["id"] == $passenger["passenger"]["id"]) {

                        $amount["passengers"][$index_amount_passenger]["amount"] = $passenger['price'];
                    }
                }
            }
            $service["amount"][$index_amount]["passengers"] = $amount["passengers"];
        }


        return $service;
    }


    private function calculatePriceService($service, $passengers)
    {
        if (count($service["amount"]) > 0) {
            $price_adult = $service["amount"][0]["price_adult"];
            $price_child = $service["amount"][0]["price_child"];

            foreach ($service["passengers"] as $index => $passenger) {
                if ($passenger["passenger"]["type"] == "ADL") {
                    $service["passengers"][$index]["amount"] = roundLito((float)$price_adult);
                }
                if ($passenger["passenger"]["type"] == "CHD") {
                    $age_child = $this->getAgeChild($passenger["passenger"]["id"], $passengers);
                    //dump("precio niño: ".$price_child);
                    //dump("servicio id: ".$service["service"]["id"]);
                    //dump("Edad niño: ".$age_child);
                    //dump("Rango de edades: ".$service["service"]["children_ages"][0]["min_age"]." - ".$service["service"]["children_ages"][0]["max_age"]);
                    if (count($service["service"]["children_ages"]) > 0 and ($age_child >= $service["service"]["children_ages"][0]["min_age"] && $age_child <= $service["service"]["children_ages"][0]["max_age"])) {
                        $service["passengers"][$index]["amount"] = roundLito((float)$price_child);
                    }
                    if (count($service["service"]["children_ages"]) > 0 and $age_child < $service["service"]["children_ages"][0]["min_age"]) {
                        $service["passengers"][$index]["amount"] = 0;
                    }
                    if (count($service["service"]["children_ages"]) > 0 and $age_child > $service["service"]["children_ages"][0]["max_age"]) {
                        $service["passengers"][$index]["amount"] = roundLito((float)$price_adult);
                    }
                }
            }

            foreach ($passengers as $index_passenger => $passenger) {
                $passengers[$index_passenger]["amount"] = 0;
                foreach ($service["passengers"] as $index_service_passenger => $service_passenger) {
                    if ($passenger["id"] == $service_passenger["passenger"]["id"]) {
                        $amount = (isset($service_passenger["amount"])) ? $service_passenger["amount"] : 0;
                        $passengers[$index_passenger]["amount"] = $amount;
                    }
                }
            }
            $service["passengers"] = $passengers;
        }

        return $service;
    }

    private function getAgeChild($id_passenger, $passengers)
    {
        foreach ($passengers as $passenger) {
            if ($passenger["id"] == $id_passenger) {
                return $passenger["age"];
            }
        }
    }

    private function checkTypePassengers($passengers, $people)
    {

        $check_count_type_null = 0;
        $count_passengers = count($passengers);
        foreach ($passengers as $passenger) {
            if (empty($passenger['type'])) {
                $check_count_type_null++;
            }
        }

        if ($check_count_type_null == $count_passengers) {

            dd("Error");  // ponte este stop porque no deberia de pasar.

            $adults = $people->adults;
            $children = $people->child;
            $count_adult = 0;
            $count_child = 0;
            foreach ($passengers as $index => $passenger) {
                if ($count_adult !== $adults) {
                    $pax = QuotePassenger::find($passenger['id']);
                    if ($pax) {
                        $pax->type = 'ADL';
                        $pax->save();
                    }
                    $passengers[$index]['type'] = 'ADL';
                    $count_adult++;
                }
            }
            foreach ($passengers as $index => $passenger) {
                if ($count_child !== $children and $passenger['type'] == null) {
                    $pax = QuotePassenger::find($passenger['id']);
                    if ($pax) {
                        $pax->type = 'CHD';
                        $pax->save();
                    }
                    $passengers[$index]['type'] = 'CHD';
                    $count_child++;
                }
            }
        }

        return $passengers;
    }
}
