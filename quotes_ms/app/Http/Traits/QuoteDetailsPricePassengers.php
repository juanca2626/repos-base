<?php

namespace App\Http\Traits;

use App\Models\QuoteCategory;
use App\Models\QuoteLog;
use App\Models\QuoteAccommodation;
use App\Models\Reservation;

trait QuoteDetailsPricePassengers
{
    use QuotesExportPassenger;

    private function getQuotePricePassenger($quote_id, $client_id, $lang, $user_type_id)
    {

        $sheets = [];
        $category_id = '';
        $editing_quote = null;
        //Buscamos la cotizacion original
        $query_log_editing_quote = QuoteLog::where('quote_id', $quote_id)->where(
            'type',
            'editing_quote'
        )->orderBy('created_at', 'desc')->first(['object_id']);
        $quote_accommodation = QuoteAccommodation::where('quote_id', $quote_id)->first();

        if ($query_log_editing_quote) {
            $editing_quote = $query_log_editing_quote->object_id;
            //Buscamos si la cotizacion original tiene un file asignado
            $reservation = Reservation::where('entity', 'Quote')
                ->where('object_id', $query_log_editing_quote->object_id)
                ->orderBy('id', 'desc')
                ->first(['id', 'file_code', 'type_class_id']);
            if ($reservation) {
                //Si lo tiene obtenemos la categoria que solo queremos cotizar
                if ($reservation->type_class_id) {
                    $category_id = $reservation->type_class_id;
                }
            }
        }

        if (!empty($category_id)) {
            $categories = QuoteCategory::where('quote_id', $quote_id)
                ->where('type_class_id', $category_id)
                ->with('type_class.translations')->get();
        } else {
            $categories = QuoteCategory::where('quote_id', $quote_id)
                ->with('type_class.translations')->get();
        }

        foreach ($categories as $category) {
            $results = $this->geneateExportPassenger($quote_id, $editing_quote, $category["id"], $client_id, $lang, $user_type_id);
            array_push($sheets, [
                'category_id' => $category["type_class_id"],
                'data'        => $this->getTransformData($results, $quote_accommodation)
            ]);
        }

        return $sheets;

    }

    private function getTransformData($details, $quote_accommodation)
    {
        // dd(json_encode($details));
        $results = [
            'headers'                   => [],
            'services'                  => [],
            'services_totals'           => [],
            'services_optionals'        => [],
            'services_optionals_totals' => [],
            'type_report'               => "",
            'sum_total'                 => 0,
        ];

        if ($details['view'] == 'exports.passengers') {
            $results = $this->getTransformDataPassenger($results, $details);
            $results['type_report'] = 'detailed';

            $total = 0;
            foreach ($results['services_totals'] as $services_totals) {
                $total = $total + $services_totals;
            }
            $results['sum_total'] = $total;
        }

        if ($details['view'] == 'exports.passengers_minimum') {
            $results = $this->getTransformDataPassengerSimple($results, $details);
            $results['type_report'] = 'summarized';


            $quote_accommodation_pax = [
                'single' => 1,
                'double' => 2,
                'triple' => 3
            ];

            $total = 0;
            foreach ($results['headers'] as $index => $headers) {
                $total = $total + (($quote_accommodation[strtolower($headers)] * $quote_accommodation_pax[strtolower($headers)]) * $results['services_totals'][$index]);
            }

            $results['sum_total'] = $total;
        }

        return $results;
    }

    private function getTransformDataPassenger($results, $details)
    {
        $data = $details["data"];
        if (isset($data["passengers"])) {

            foreach ($data["passengers"] as $p => $passenger) {
                if ($passenger["first_name"] != "") {
                    array_push($results['headers'], $passenger["first_name"] ." ". $passenger["last_name"]  ." - ".$passenger["type"]);
                } else {
                    if ($passenger["type"] == "ADL") {
                        if ($data["lang"] === 'es') {
                            array_push($results['headers'], "Adulto ". $passenger["index"]);
                        } elseif ($data["lang"] === 'pt') {
                            array_push($results['headers'], "Adulto ". $passenger["index"]);
                        } else {
                            array_push($results['headers'], "Adult ". $passenger["index"]);
                        }
                    } else {
                        if ($data["lang"] === 'es') {
                            array_push($results['headers'], "Niño ". $passenger["index"] ."( ".$passenger["age"] ." años)");
                        } elseif ($data["lang"] === 'pt') {
                            array_push($results['headers'], "Niño ". $passenger["index"] ."( ".$passenger["age"] ." anos)");
                        } else {
                            array_push($results['headers'], "Child ". $passenger["index"] ."( ".$passenger["age"] ." years)");
                        }
                    }
                }
                array_push($results['services_totals'], roundLito((float)$passenger["total"]));
                array_push($results['services_optionals_totals'], roundLito((float)$passenger["total_optional"]));
            }
        }

        $services = count($data["categories"]) > 0 ? $data["categories"][0]["services"] : [];
        $serviceTransfor = [];
        foreach ($services as $service) {

            if (in_array($service['type'], ['service', 'hotel'])) {
                if (isset($service["amount"]) and count($service["amount"]) > 0) {
                    $amount = $service["amount"][0];
                    $serviceTransfor[$amount["date_service"]][] = $this->getServicePassengers($service);
                }
            }
        }
        foreach ($serviceTransfor as $date_in => $service) {
            array_push($results['services'], [
                'date'     => $date_in,
                'services' => $service
            ]);
        }

        $services_optionals = count($data["categories_optional"]) > 0 ? $data["categories_optional"][0]["services"] : [];
        $serviceTransfor = [];
        foreach ($services_optionals as $service) {
            if (in_array($service['type'], ['service', 'hotel'])) {
                if (isset($service["amount"]) and count($service["amount"]) > 0) {
                    $amount = $service["amount"][0];
                    $serviceTransfor[$amount["date_service"]][] = $this->getServicePassengers($service);
                }
            }
        }
        foreach ($serviceTransfor as $date_in => $service) {
            array_push($results['services_optionals'], [
                'date'     => $date_in,
                'services' => $service
            ]);
        }

        return $results;
    }

    private function getServicePassengers($service)
    {

        if ($service["type"] == "service") {
            $item = [
                "type"         => $service["type"],
                "code"         => $service["service"]["aurora_code"],
                "descriptions" => $service["service"]["service_translations"][0]["name"],
                "order"        => 1,
                "columns"      => []
            ];

            foreach ($service["passengers"] as $key => $passenger) {
                array_push($item['columns'], roundLito((float)$passenger['amount']));
            }
        }

        if ($service["type"] == "hotel") {

            $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];

            if ($total_accommodations > 0) {

                $room_type = !empty($service["service_rooms"]) ? $this->clearNameRoom($service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"]) : "N/A";
                $meal = !empty($service["service_rooms"]) ? $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal"]["translations"][0]["value"] : "N/A";

                $item = [
                    "type"         => $service["type"],
                    "code"         => $service["hotel"]["channel"][0]["code"],
                    "descriptions" => $service["hotel"]["name"],
                    "room_type"    => $room_type,
                    "meal"         => $meal,
                    "order"        => 2,
                    "columns"      => []
                ];

                $amount = $service["amount"][0];
                foreach ($amount["passengers"] as $key => $passenger) {
                    array_push($item['columns'], roundLito((float)$passenger['amount']));
                }
            }

        }

        return $item;
    }

    private function getTransformDataPassengerSimple($results, $details)
    {
        $data = $details["data"];
        if (isset($data["accommodations"])) {
            if (isset($data["accommodations"]["single"]) and $data["accommodations"]["single"] > 0) {
                array_push($results['headers'], 'Single');
            }
            if (isset($data["accommodations"]["double"]) and $data["accommodations"]["double"] > 0) {
                array_push($results['headers'], 'Double');
            }
            if (isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"] > 0) {
                array_push($results['headers'], 'Triple');
            }
        }

        $services = count($data["categories"]) > 0 ? $data["categories"][0]["services"] : [];
        $services_totals = count($data["categories"]) > 0 ? $data["categories"][0]["services_total"] : [];

        if (isset($data["accommodations"])) {
            if (isset($data["accommodations"]["single"]) and $data["accommodations"]["single"] > 0) {
                array_push($results['services_totals'], roundLito((float)$services_totals['single']));
            }
            if (isset($data["accommodations"]["double"]) and $data["accommodations"]["double"] > 0) {
                array_push($results['services_totals'], roundLito((float)$services_totals['double']));
            }
            if (isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"] > 0) {
                array_push($results['services_totals'], roundLito((float)$services_totals['triple']));
            }
        }

        $serviceTransfor = [];
        foreach ($services as $service) {
            if (in_array($service['type'], ['service', 'hotel'])) {
                if (isset($service["amount"]) and count($service["amount"]) > 0) {
                    $amount = $service["amount"][0];
                    $serviceTransfor[$amount["date_service"]][] = $this->getServiceSimple($service, $data["accommodations"]);
                } else {
                    \Log::debug(json_encode($data));
                }
            }
        }
        foreach ($serviceTransfor as $date_in => $service) {
            array_push($results['services'], [
                'date'     => $date_in,
                'services' => $service
            ]);
        }

        $services_optionals = count($data["categories_optional"]) > 0 ? $data["categories_optional"][0]["services"] : [];
        $services_optionals_totals = count($data["categories_optional"]) > 0 ? $data["categories_optional"][0]["services_total"] : [];
        if (isset($data["accommodations"])) {
            if (isset($data["accommodations"]["single"]) and $data["accommodations"]["single"] > 0) {
                array_push($results['services_optionals_totals'], roundLito((float)$services_optionals_totals['single']));
            }
            if (isset($data["accommodations"]["double"]) and $data["accommodations"]["double"] > 0) {
                array_push($results['services_optionals_totals'], roundLito((float)$services_optionals_totals['double']));
            }
            if (isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"] > 0) {
                array_push($results['services_optionals_totals'], roundLito((float)$services_optionals_totals['triple']));
            }
        }

        $serviceTransfor = [];
        foreach ($services_optionals as $service) {
            if (in_array($service['type'], ['service', 'hotel'])) {
                if (isset($service["amount"]) and count($service["amount"]) > 0) {
                    $amount = $service["amount"][0];
                    $serviceTransfor[$amount["date_service"]][] = $this->getServiceSimple($service, $data["accommodations"]);
                }
            }
        }
        foreach ($serviceTransfor as $date_in => $service) {
            array_push($results['services_optionals'], [
                'date'     => $date_in,
                'services' => $service
            ]);
        }

        return $results;
    }
    private function getServiceSimple($service, $accommodations)
    {

        if ($service["type"] == "service") {
            $item = [
                "type"         => $service["type"],
                "code"         => $service["service"]["aurora_code"],
                "descriptions" => $service["service"]["service_translations"][0]["name"],
                "order"        => 1,
                "columns"      => []
            ];

            if (isset($accommodations)) {
                if (isset($accommodations["single"]) and $accommodations["single"] > 0) {
                    array_push($item['columns'], $service["single_import"]);
                }
                if (isset($accommodations["double"]) and $accommodations["double"] > 0) {
                    array_push($item['columns'], $service["double_import"]);
                }
                if (isset($accommodations["triple"]) and $accommodations["triple"] > 0) {
                    array_push($item['columns'], $service["triple_import"]);
                }
            }

        }

        if ($service["type"] == "hotel") {
            $room_type = !empty($service["service_rooms"]) ? $this->clearNameRoom($service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"]) : "N/A";
            $meal = !empty($service["service_rooms"]) ? $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal"]["translations"][0]["value"] : "N/A";

            $item = [
                "type"         => $service["type"],
                "code"         => $service["hotel"]["channel"][0]["code"],
                "descriptions" => $service["hotel"]["name"],
                "room_type"    => $room_type,
                "meal"         => $meal,
                "order"        => 2,
                "columns"      => []
            ];
            $amount = $service["amount"][0];
            if (isset($accommodations)) {
                if (isset($accommodations["single"]) and $accommodations["single"] > 0) {
                    array_push($item['columns'], $amount["single"]);
                }
                if (isset($accommodations["double"]) and $accommodations["double"] > 0) {
                    array_push($item['columns'], $amount["double"]);
                }
                if (isset($accommodations["triple"]) and $accommodations["triple"] > 0) {
                    array_push($item['columns'], $amount["triple"]);
                }
            }

        }

        return $item;
    }

    private function clearNameRoom($name)
    {
        $name = str_replace("SGL", "", $name);
        $name = str_replace("DBL", "", $name);
        $name = str_replace("TPL", "", $name);
        $name = str_replace("MAT", "", $name);
        $name = str_replace("MATRIMONIAL", "", $name);
        $name = str_replace("+ CAMA ADICIONAL", "", $name);
        $name = str_replace("+ ADD BED", "", $name);
        $name = str_replace("+ SOFA CAMA", "", $name);
        $name = str_replace("TRP", "", $name);
        $name = str_replace("+ CAMA ADD", "", $name);
        $name = str_replace("SIMPLE", "", $name);

        return  $name;
    }

}
