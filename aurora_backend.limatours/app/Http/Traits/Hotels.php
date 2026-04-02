<?php


namespace App\Http\Traits;


trait Hotels
{
    private function checkCountryState($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["hotel"]["country"]["iso"].','.$hotel_client["hotel"]["state"]["iso"],
                    "description" => $hotel_client["hotel"]["country"]["translations"][0]["value"].",".$hotel_client["hotel"]["state"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel_client["hotel"]["country"]["iso"].','.$hotel_client["hotel"]["state"]["iso"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["hotel"]["country"]["iso"].','.$hotel_client["hotel"]["state"]["iso"],
                        "description" => $hotel_client["hotel"]["country"]["translations"][0]["value"].",".$hotel_client["hotel"]["state"]["translations"][0]["value"],
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStateCity($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" => $hotel_client["hotel"]["country"]["iso"].','.$hotel_client["hotel"]["state"]["iso"].','.$hotel_client["hotel"]["city_id"],
                    "description" => $hotel_client["hotel"]["country"]["translations"][0]["value"].', '.$hotel_client["hotel"]["state"]["translations"][0]["value"].', '.$hotel_client["hotel"]["city"]["translations"][0]["value"],
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == $hotel_client["hotel"]["country"]["iso"].','.$hotel_client["hotel"]["state"]["iso"].','.$hotel_client["hotel"]["city_id"]) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" => $hotel_client["hotel"]["country"]["iso"].','.$hotel_client["hotel"]["state"]["iso"].','.$hotel_client["hotel"]["city_id"],
                        "description" => $hotel_client["hotel"]["country"]["translations"][0]["value"].', '.$hotel_client["hotel"]["state"]["translations"][0]["value"].', '.$hotel_client["hotel"]["city"]["translations"][0]["value"],
                    ]);
                }
            }
        }

        return $destinations;
    }

    private function checkCountryStateCityDistrict($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {

            $id_string_country_id = $hotel_client["hotel"]["country"]["iso"];

            $id_string_state_id = ",".$hotel_client["hotel"]["state"]["iso"];

            $id_string_city_id = $hotel_client["hotel"]["city_id"] ? ",".$hotel_client["hotel"]["city_id"] : '';

            $id_string_district_id = $hotel_client["hotel"]["district_id"] ? ",".$hotel_client["hotel"]["district_id"] : '';

            $ids_string = $id_string_country_id.$id_string_state_id.$id_string_city_id.$id_string_district_id;

            $country_name = $hotel_client["hotel"]["country"]["translations"][0]["value"];
            $state_name = $hotel_client["hotel"]["state"]["translations"][0]["value"] ? ','.$hotel_client["hotel"]["state"]["translations"][0]["value"] : '';
            $city_name = $hotel_client["hotel"]["city"]["translations"][0]["value"] ? ','.$hotel_client["hotel"]["city"]["translations"][0]["value"] : '';
            $district_name = (!empty($hotel_client["hotel"]["district"]["translations"]) and $hotel_client["hotel"]["district"]["translations"][0]["value"]) ? ','.$hotel_client["hotel"]["district"]["translations"][0]["value"] : '';
            if ($id_string_district_id) {
                if (count($destinations) === 0) {
                    array_push($destinations, [
                        "ids" => $ids_string,
                        "description" => $country_name.$state_name.$city_name.$district_name,
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
                            "description" => $country_name.$state_name.$city_name.$district_name,
                        ]);
                    }
                }
            }
        }

        return $destinations;
    }

}
