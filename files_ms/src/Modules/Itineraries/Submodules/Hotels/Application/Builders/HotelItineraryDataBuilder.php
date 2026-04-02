<?php

namespace Src\Modules\Itineraries\SubModules\Hotels\Application\Builders;

use Carbon\Carbon;

class HotelItineraryDataBuilder
{
    public function build(int $fileId, array $hotel, ?bool $file_exist = null): array
    {
        

        $confirmation_status = true;
        foreach($hotel['reservations_hotel_rooms'] as $hotelRooms){
            if($hotelRooms['onRequest'] === 0){
                $confirmation_status = false;
            }

        }

        $city_in_iso = $hotel['hotel']['city']['iso'];
        if(!$city_in_iso){
            $city_in_iso = $hotel['hotel']['state']['iso'];
            if(!$city_in_iso){
                $city_in_iso = "LIM";
            }
        }

        $protected_rate = false; 
        foreach($hotel['reservations_hotel_rooms'] as $room){
            $check_in = $room['check_in'];
            foreach($room['rates_plans_room']['date_range_hotel'] as $dateRanges){
                if( $check_in >= $dateRanges['date_from'] and $check_in<=$dateRanges['date_to']){
                    if($dateRanges['flag_migrate'] == "1"){
                        $protected_rate = true;
                    }
                }
            } 
        }

        $category = "";
        if(isset($hotel['hotel']['hoteltypeclass'])){
            if(is_array($hotel['hotel']['hoteltypeclass']) and count($hotel['hotel']['hoteltypeclass'])>0){
                $category = $hotel['hotel']['hoteltypeclass'][0]['typeclass']['translations'][0]['value'];
            }
        }

        $add_to_statement = true;
        if($file_exist !== null){
            $add_to_statement = false;
        }

        return [
            'id' => null,
            'file_id' => $fileId,
            'entity' => 'hotel',
            'object_id' => $hotel['hotel_id'],
            'name' => $hotel['hotel_name'],
            'category' => $category,
            'object_code' => $hotel['hotel_code'],
            'country_in_iso' => $hotel['hotel']['country']['iso'],
            'country_in_name' => $hotel['hotel']['country']['translations'][0]['value'],
            'city_in_iso' => $city_in_iso,
            'city_in_name' => $hotel['hotel']['city']['translations'][0]['value'],
            'zone_in_iso' => null,
            'country_out_iso' => $hotel['hotel']['country']['iso'],
            'country_out_name' => $hotel['hotel']['country']['translations'][0]['value'],
            'city_out_iso' => $city_in_iso,
            'city_out_name' => $hotel['hotel']['city']['translations'][0]['value'],
            'zone_out_iso' => null,
            'start_time' => $hotel['check_in_time'],
            'departure_time' => $hotel['check_out_time'],
            'city_name' => $hotel['hotel']['city']['translations'][0]['value'] ?? null,
            'country_name' => $hotel['hotel']['country']['translations'][0]['value'] ?? null,
            'date_in' => $hotel['check_in'],
            'date_out' => $hotel['check_out'],
            'total_adults' => 0,
            'total_children' => 0,
            'total_infants' => 0,
            'markup_created' => $hotel['reservations_hotel_rooms'][0]['markup'],
            'total_amount' => $hotel['total_amount'],
            'total_cost_amount' => 0,
            'profitability' => 0,
            'serial_sharing' => 0,
            'executive_code' => $hotel['executive_code'],
            'status' => true,
            'confirmation_status' => $confirmation_status,
            'protected_rate' => $protected_rate,
            'view_protected_rate' => false,
            'is_in_ope' => 0,
            'sent_to_ope' => 0,
            'hotel_origin' => NULL,
            'hotel_destination' => NULL, 
            'add_to_statement' => $add_to_statement,
            'aurora_reservation_id' => $hotel['id'],
            'files_ms_parameters' => isset($hotel['files_ms_parameters']) ? $hotel['files_ms_parameters'] : null
        ];


    }
}