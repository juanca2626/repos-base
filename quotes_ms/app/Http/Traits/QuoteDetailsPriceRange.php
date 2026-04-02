<?php

namespace App\Http\Traits;

use App\Models\Client;
use App\Models\Language;
use App\Models\Quote;
use App\Models\QuoteCategory;
use App\Models\QuoteDynamicSaleRate;
use App\Models\QuoteRange;
use App\Models\QuoteService;
use App\Models\QuoteLog;

trait QuoteDetailsPriceRange
{
    private function getQuotePriceRanger($quote_id, $client_id, $lang)
    {

        $this->updateAmountAllServices($quote_id, $client_id);
        $quote_name = \App\Models\Quote::where('id', $quote_id)->first()->name;
        $quote_name = str_replace("/", "-", $quote_name);


        $sheets = [];

        $query_log_editing_quote = QuoteLog::where('quote_id', $quote_id)->where(
            'type',
            'editing_quote'
        )->orderBy('created_at', 'desc')->first(['object_id']);
        $editing_quote = null;
        if ($query_log_editing_quote) {
            $editing_quote = $query_log_editing_quote->object_id;
        }

        $categories = QuoteCategory::where('quote_id', $quote_id)->with('type_class.translations')->get();

        foreach ($categories as $category) {
            $results = $this->geneateExportRanger($quote_id, $category["id"], $client_id, $lang, $editing_quote);
            array_push($sheets, [
                'category_id' => $category["type_class_id"],
                'data'        => $this->getTransformDataRange($results)
            ]);

        }

        return $sheets;


        // return Excel::download(new  \App\Exports\CategoryExport($quote_id, $client_id, $lang), $quote_name . '.xlsx');

    }

    private function getTransformDataRange($details)
    {
        $data = $details["data"];
        $results = [
            'headers'                   => [],
            'services'                  => [],
            'services_totals'           => [],
            'services_optionals'        => [],
            'services_optionals_totals' => [],
            'sum_total'                 => 0,

        ];

        $results['headers'] = $this->getHeaderServiceRanges($data["ranges_quote"], $data["accommodation"]);
        $results['services_totals'] = $this->getTotalServiceRanges($data["ranges_quote"], $data["accommodation"]);

        $services = count($data["categories"]) > 0 ? $data["categories"][0]["services"] : [];
        $serviceTransfor = [];
        foreach ($services as $service) {
            $serviceTransfor[$service["date_service"]][] = $this->getServiceRanges($service, $data["accommodation"], "ranges");
        }
        foreach ($serviceTransfor as $date_in => $service) {
            array_push($results['services'], [
                'date'     => $date_in,
                'services' => $service
            ]);
        }

        $results['services_optionals_totals'] = $this->getTotalServiceRanges($data["ranges_quote_optional"], $data["accommodation"]);

        $services = count($data["categories_optional"]) > 0 ? $data["categories_optional"][0]["services_optional"] : [];
        $serviceTransfor = [];
        foreach ($services as $service) {
            $serviceTransfor[$service["date_service"]][] = $this->getServiceRanges($service, $data["accommodation"], "ranges_optional");
        }
        foreach ($serviceTransfor as $date_in => $service) {
            array_push($results['services_optionals'], [
                'date'     => $date_in,
                'services' => $service
            ]);
        }

        $total = 0;
        foreach ($results['services_totals'] as $services_totals) {
            $total = $total + $services_totals;
        }
        $results['sum_total'] = $total;

        return $results;
    }


    private function getHeaderServiceRanges($ranges_quote, $accommodations)
    {

        $headers = [];
        foreach ($ranges_quote as $range) {
            $head = [
                'head'   => "Min {$range["from"]} - {$range["to"]}",
                'ranges' => []
            ];

            if ($accommodations["single"] == "1") {
                array_push($head['ranges'], 'Single Room');
            }
            if ($accommodations["double"] == "1") {
                array_push($head['ranges'], 'Double Room');
            }
            if ($accommodations["triple"] == "1") {
                array_push($head['ranges'], 'Triple Room');
            }
            array_push($headers, $head);
        }

        return $headers;
    }

    private function getTotalServiceRanges($ranges_quote, $accommodations)
    {
        $services_totals = [];
        foreach ($ranges_quote as $range) {

            if ($accommodations["single"] == "1") {
                array_push($services_totals, $range["simple"]);
            }
            if ($accommodations["double"] == "1") {
                array_push($services_totals, $range["double"]);
            }
            if ($accommodations["triple"] == "1") {
                array_push($services_totals, $range["triple"]);
            }
        }

        return $services_totals;
    }

    private function getServiceRanges($service, $accommodations, $range)
    {

        if ($service["type"] == "service") {
            $item = [
                "type"         => $service["type"],
                "code"         => $service["service_code"],
                "descriptions" => $service["service_name"],
                "columns"      => []
            ];

            foreach ($service[$range] as $range_service) {

                if (isset($accommodations)) {
                    if (isset($accommodations["single"]) and $accommodations["single"] > 0) {
                        array_push($item['columns'], $range_service["simple"]);
                    }
                    if (isset($accommodations["double"]) and $accommodations["double"] > 0) {
                        array_push($item['columns'], $range_service["double"]);
                    }
                    if (isset($accommodations["triple"]) and $accommodations["triple"] > 0) {
                        array_push($item['columns'], $range_service["triple"]);
                    }
                }

            }



        }

        if ($service["type"] == "hotel") {
            $item = [
                "type"         => $service["type"],
                "code"         => $service["service_code"],
                "descriptions" => clearNameRoom($service["service_name"]),
                // "room_type" => $this->clearNameRoom($service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"]),
                "meal"    => $service["rate_meals"],
                "columns" => []
            ];

            foreach ($service[$range] as $range_service) {

                if (isset($accommodations)) {
                    if (isset($accommodations["single"]) and $accommodations["single"] > 0) {
                        array_push($item['columns'], $range_service["simple"]);
                    }
                    if (isset($accommodations["double"]) and $accommodations["double"] > 0) {
                        array_push($item['columns'], $range_service["double"]);
                    }
                    if (isset($accommodations["triple"]) and $accommodations["triple"] > 0) {
                        array_push($item['columns'], $range_service["triple"]);
                    }
                }

            }

        }

        return $item;
    }

    private function geneateExportRanger(
        $quote_id = null,
        $quote_category_id = null,
        $client_id = null,
        $lang = null,
        $quote_original_id = null
    ) {

        if (!empty($quote_original_id)) {
            $_quote_id = $quote_original_id;
        }

        $data = [
            'quote_id'              => $_quote_id,
            'quote_name'            => "",
            'accommodation'         => [],
            'client_code'           => "",
            'client_name'           => "",
            'lang'                  => "",
            'ranges_quote'          => [],
            'ranges_quote_optional' => [],
            'categories'            => [],
            'categories_optional'   => []
        ];

        $client = Client::where('id', $client_id)->first();
        $quote = Quote::with('accommodation')->where('id', $quote_id)->first();
        $markup = $quote->markup;

        $data['quote_name'] = $quote->name;
        $data['accommodation'] = $quote->accommodation->toArray();
        $data['client_code'] = (isset($client->code)) ? $client->code : '';
        $data['client_name'] = (isset($client->name)) ? $client->name : '';
        $data['lang'] = $lang;
        $language_id = Language::where('iso', $lang)->first()->id;
        // = "";
        $category = QuoteCategory::where('id', $quote_category_id)->where(
            'quote_id',
            $quote_id
        )->with('type_class.translations')->first();
        $ranges_quote = QuoteRange::where('quote_id', $quote_id)->orderBy('from')->get();
        $ranges_quote_optional = QuoteRange::where('quote_id', $quote_id)->orderBy('from')->get();
        foreach ($ranges_quote as $range_quote) {
            $quote_service_ids = QuoteService::where('quote_category_id', $category["id"])
                ->where('optional', 0)
                ->where('locked', 0)
                ->pluck('id');
            $amount_range = QuoteDynamicSaleRate::where(
                'quote_category_id',
                $category["id"]
            )->whereIn('quote_service_id', $quote_service_ids)->where(
                'pax_from',
                $range_quote["from"]
            )->where('pax_to', $range_quote["to"]);
            $simple = $amount_range->sum('simple');
            $double = $amount_range->sum('double');
            $triple = $amount_range->sum('triple');
            array_push($data['ranges_quote'], [
                'from' => $range_quote["from"],
                'to'   => $range_quote["to"],
                // 'amount' => roundLito((float)$simple),
                'amount' => 0,
                'simple' => roundLito((float)$simple),
                'double' => roundLito((float)$double),
                'triple' => roundLito((float)$triple),
            ]);
        }
        foreach ($ranges_quote_optional as $range_quote) {
            $quote_service_ids = QuoteService::where('quote_category_id', $category["id"])
                ->where('optional', 1)
                ->where('locked', 0)
                ->pluck('id');
            $amount_range = QuoteDynamicSaleRate::where(
                'quote_category_id',
                $category["id"]
            )->whereIn('quote_service_id', $quote_service_ids)->where(
                'pax_from',
                $range_quote["from"]
            )->where('pax_to', $range_quote["to"]);
            $simple = $amount_range->sum('simple');
            $double = $amount_range->sum('double');
            $triple = $amount_range->sum('triple');
            array_push($data['ranges_quote_optional'], [
                'from' => $range_quote["from"],
                'to'   => $range_quote["to"],
                // 'amount' => roundLito((float)$amount_range)
                'amount' => 0,
                'simple' => roundLito((float)$simple),
                'double' => roundLito((float)$double),
                'triple' => roundLito((float)$triple),
            ]);
        }
        array_push($data["categories"], [
            'category' => $category['type_class']["translations"][0]['value'],
            'services' => []
        ]);
        array_push($data["categories_optional"], [
            'category'          => $category['type_class']["translations"][0]['value'],
            'services_optional' => []
        ]);
        $quote_services = QuoteService::where('quote_category_id', $category["id"])
            ->where('optional', 0)
            ->where('locked', 0)
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with('hotel.channel')
            ->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.room.room_type',
                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])
            ->orderBy('date_in')->get();
        $quote_services_optional = QuoteService::where('quote_category_id', $category["id"])
            ->where('optional', 1)
            ->where('locked', 0)
            ->with([
                'service' => function ($query) use ($language_id) {
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with('hotel.channel')
            ->with([
                'service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
                'service_rooms.rate_plan_room.room.room_type',
                'service_rooms.rate_plan_room.rate_plan.meal.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                },
            ])
            ->orderBy('date_in')->get();

        foreach ($quote_services as $quote_service) {
            $ranges = QuoteDynamicSaleRate::where('quote_category_id', $category["id"])->where(
                'quote_service_id',
                $quote_service["id"]
            )->orderBy('date_service')->orderBy('pax_from')->get();

            if (count($ranges) > 0) {  // este filtra solo los servicios que tienen ranger.
                if ($quote_service["type"] == "service") {
                    array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                        'date_service'   => $ranges[0]["date_service"],
                        'date_in_format' => convertDate($ranges[0]["date_service"], '/', '-', 1),
                        'type'           => $quote_service["type"],
                        'service_code'   => $quote_service["service"]["aurora_code"],
                        'service_name'   => $quote_service["service"]["service_translations"][0]["name"],
                        'ranges'         => $ranges
                    ]);
                }
                if ($quote_service["type"] == "hotel") {
                    $date_services = $ranges->groupBy('date_service');

                    $name_room = '';
                    if (count($quote_service["service_rooms"]) > 0) {
                        $name_room = " - " . $quote_service["service_rooms"][0]['rate_plan_room']['room']['translations'][0]['value'];
                    }
                    foreach ($date_services as $date_service => $ranges) {
                        array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                            'date_service'   => $date_service,
                            'service_id'     => $quote_service["id"],
                            'hotel_id'       => $quote_service["object_id"],
                            'date_in_format' => convertDate($date_service, '/', '-', 1),
                            'type'           => $quote_service["type"],
                            'service_code'   => $quote_service["hotel"]["channel"][0]["code"],
                            'service_name'   => $quote_service["hotel"]["name"].$name_room,
                            'room_types'     => $ranges[0]['room_types'],
                            'rate_meals'     => $ranges[0]['rate_meals'],
                            'ranges'         => $ranges
                        ]);
                    }
                }
            }
        }
        foreach ($quote_services_optional as $quote_service) {
            $ranges = QuoteDynamicSaleRate::where('quote_category_id', $category["id"])->where(
                'quote_service_id',
                $quote_service["id"]
            )->orderBy('pax_from')->get();

            if (count($ranges) > 0) {
                if ($quote_service["type"] == "service") {
                    array_push(
                        $data["categories_optional"][count($data["categories_optional"]) - 1]["services_optional"],
                        [
                            'date_service'    => $ranges[0]["date_service"],
                            'date_in_format'  => convertDate($ranges[0]["date_service"], '/', '-', 1),
                            'type'            => $quote_service["type"],
                            'service_code'    => $quote_service["service"]["aurora_code"],
                            'service_name'    => $quote_service["service"]["service_translations"][0]["name"],
                            'ranges_optional' => $ranges
                        ]
                    );
                }
                if ($quote_service["type"] == "hotel") {
                    $date_services = $ranges->groupBy('date_service');

                    $name_room = '';

                    foreach ($date_services as $date_service => $ranges) {
                        array_push(
                            $data["categories_optional"][count($data["categories_optional"]) - 1]["services_optional"],
                            [
                                'date_service'    => $date_service,
                                'service_id'      => $quote_service["id"],
                                'hotel_id'        => $quote_service["object_id"],
                                'date_in_format'  => convertDate($date_service, '/', '-', 1),
                                'type'            => $quote_service["type"],
                                'service_code'    => $quote_service["hotel"]["channel"][0]["code"],
                                'service_name'    => $quote_service["hotel"]["name"].$name_room,
                                'room_types'      => $ranges[0]['room_types'],
                                'rate_meals'      => $ranges[0]['rate_meals'],
                                'ranges_optional' => $ranges
                            ]
                        );
                    }
                }
            }
        }
        // echo "<pre>";
        // print_r($data);
        // die('..');
        // dd($data);


        return [
            'data'            => $this->orderQuoteRange($data),
            'discount'        => $quote->discount,
            'discount_detail' => $quote->discount_detail
        ];

    }

    public function orderQuoteRange($data)
    {

        foreach ($data["categories"] as $index => $category) {
            $data["categories"][$index]["services"] = $this->orderQuoteRangeItem($category, "services");
        }

        foreach ($data["categories_optional"] as $index => $category) {
            $data["categories_optional"][$index]["services_optional"] = $this->orderQuoteRangeItem($category, "services_optional");
        }

        return $data;
    }

    public function orderQuoteRangeItem($category, $fields)
    {

        $quote_services_edit = [];
        $quote_services = collect($category[$fields])->groupBy('date_in_format');
        foreach ($quote_services as $date => $quoteServices) {

            foreach ($quoteServices as $service) {

                if ($service['type'] == "hotel") {

                    $service['order_general'] = 1;
                    $quote_services_edit[$date][] = $service;

                } else {
                    $service['order_general'] = 0;
                    $quote_services_edit[$date][] = $service;
                }

            }

        }

        // dd($quote_services_edit);
        $newQuoteServices = [];
        foreach ($quote_services_edit as $date => $services) {

            $date_in = array_column($services, 'date_in_format');
            $order = array_column($services, 'order_general');
            array_multisort($date_in, SORT_ASC, $order, SORT_ASC, $services);

            foreach ($services as $service) {
                array_push($newQuoteServices, $service);
            }
        }

        return $newQuoteServices;
    }

}
