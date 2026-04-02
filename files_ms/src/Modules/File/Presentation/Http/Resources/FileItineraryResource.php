<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $results =  [
            'id' => $this->resource['id'],
            'entity' => $this->resource['entity'],
            'name'=> $this->resource['name'],
            'category'=> $this->resource['category'],
            'object_id'=> $this->resource['object_id'],
            'object_code'=> $this->resource['object_code'],
            'country'=> $this->resource['country_in_name'],
            'date_in'=> $this->resource['date_in'],
            'date_out'=> $this->resource['date_out'],
            'start_time'=> $this->resource['start_time'],
            'departure_time'=> $this->resource['departure_time'],
            'adults' => $this->resource['total_adults'],
            'children' => $this->resource['total_children'],
            'total_amount'=> $this->resource['total_amount'],
            'total_cost_amount' => $this->resource['total_cost_amount'],
            'status' => $this->resource['status'],
            'confirmation_status' => $this->resource['confirmation_status'],
            'descriptions'=> isset($this->resource['descriptions']) ? FileItineraryDescriptionResource::collection(
                $this->resource['descriptions']
            ) :[],
            'profitability' => $this->resource['profitability'],
            'is_in_ope' => $this->resource['is_in_ope'],
            'sent_to_ope' => $this->resource['sent_to_ope'],
            'protected_rate' => $this->resource['protected_rate'],
            'view_protected_rate' => $this->resource['view_protected_rate'],
            'add_to_statement' => $this->resource['add_to_statement'],
            'country_in_iso' => $this->resource['country_in_iso'],
            'country_in_name' => $this->resource['country_in_name'],
            'city_in_iso' => $this->resource['city_in_iso'],
            'city_in_name' => $this->resource['city_in_name'],
            'country_out_iso' => $this->resource['country_out_iso'],
            'country_out_name' => $this->resource['country_out_name'],
            'city_out_iso' => $this->resource['city_out_iso'],
            'city_out_name' => $this->resource['city_out_name'],
            'zone_in_id' => $this->resource['zone_in_id'],
            'zone_out_id' => $this->resource['zone_out_id']
        ];
 
        if($this->resource['entity'] == "hotel") {
 
            $results["room_amount_logs"] = isset($results["room_amount_logs"]) ? FileItineraryRoomAmountLogResource::collection($this->resource['room_amount_logs']) : [];
            $results["rooms"] = FileHotelRoomResource::collection($this->resource['rooms']);

            $hyperguest = true;

            foreach($this->resource['rooms'] as $room)
            {
                if(trim($room['channel_reservation_code_master']) == ''){
                    $hyperguest = false;
                }
            }
            $results["hyperguest"] = $hyperguest;
        }

        // aqui .....................

        if($this->resource['entity'] == "service" || $this->resource['entity'] == "service-temporary") {


            // $results["city_in"] = $this->resource['cityInName'];
            // $results["city_out"] = $this->resource['cityOutName'];
            $results["zone_in_airport"] = $this->resource['zone_in_airport'];
            $results["zone_out_airport"] = $this->resource['zone_out_airport'];
            $results["hotel_origin"] = $this->resource['hotel_origin'] ? $this->resource['hotel_origin'] : '';
            $results["hotel_destination"] = $this->resource['hotel_destination'] ? $this->resource['hotel_destination']: '';
            $results['service_category_id'] =  $this->resource['service_category_id'];
            $results['service_sub_category_id'] =  $this->resource['service_sub_category_id'];
            $results['service_type_id'] =  $this->resource['service_type_id'];
            $results['service_summary'] = $this->resource['service_summary'];
            $results['service_itinerary'] = $this->resource['service_itinerary'];
            $results["service_amount_logs"] = isset($results["service_amount_logs"]) ?  FileItineraryServiceAmountLogResource::collection(
                $this->resource['service_amount_logs']
            ) :[];

            $results["show_master_services"] = count($this->resource['services'])>0 ? true : false;
            $results["services"] = FileServiceResource::collection($this->resource['services']);
            
            if($this->resource['entity'] == "service")
            {
                $results['accommodations'] = isset($this->resource['accommodations']) ? FileItineraryAccommodationResource::collection(
                    $this->resource['accommodations']
                ): [];
            }

            $results['is_programmable'] = true;
            $results['show_master_services'] = true;
            if(count($this->resource['services']) == 0)
            {
                $results['show_master_services'] = false;                
            }  
        }

        if($this->resource['entity'] == "service-mask") {
            $results['service_supplier_code'] = $this->resource['service_supplier_code'];
            $results['service_supplier_name'] = $this->resource['service_supplier_name'];
            $results['details'] = FileItineraryDetailResource::collection(
                $this->resource['details']
            );
            $results['accommodations'] = isset($this->resource['accommodations']) ? FileItineraryAccommodationResource::collection(
                $this->resource['accommodations']
            ) : [];

        }


        // --------------------------

        if($this->resource['entity'] == "flight") {
            $results['flights'] = isset($this->resource['flights']) ?  FileItineraryFlightFlightResource::collection($this->resource['flights']) : [];
            $results['is_programmable'] = true;
        }

        return $results;
    }


}
