<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileTemporaryServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {        
        $results =  [
            'id' => $this->resource->id,
            'entity' => $this->resource->entity->value(),
            'name'=> $this->resource->name->value(),
            'category'=> $this->resource->category->value(),
            'object_id'=> $this->resource->objectId->value(),
            'object_code'=> $this->resource->serviceCode->value(),
            'country'=> $this->resource->countryInName->value(),    
            'date_in'=> $this->resource->dateIn->value(),
            'date_out'=> $this->resource->dateOut->value(),
            'start_time'=> $this->resource->startTime->value(),
            'departure_time'=> $this->resource->departureTime->value(),
            'adults' => $this->resource->totalAdults->value(),
            'children' => $this->resource->totalChildren->value(),
            'total_amount'=> $this->resource->totalAmount->value(),
            'total_cost_amount' => $this->resource->totalCostAmount->value(),
            'status' => $this->resource->status->value(),
            'confirmation_status' => $this->confirmationStatus->value() 
        ];
 
        $results["city_in_iso"] = $this->resource->cityInIso->value();   
        $results["city_out_iso"] = $this->resource->cityOutIso->value(); 

        $results["zone_in_airport"] = $this->resource->zoneInAirport->value();              
        $results["zone_out_airport"] = $this->resource->zoneOutAirport->value();

        $results["hotel_origin"] = $this->resource->hotelOrigin->value() ? $this->resource->hotelOrigin->value() : '';
        $results["hotel_destination"] = $this->resource->hotelDestination->value() ? $this->resource->hotelDestination->value(): ''; 
        $results["services"] = FileTemporaryMasterServiceResource::collection($this->services->jsonSerialize());
        $results['details'] = FileTemporaryServiceDetailResource::collection(
            $this->details->jsonSerialize()
        );
  

        return $results;
    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray_bk(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'file_id' => $this->resource->fileId->value(),
            'entity' => $this->resource->entity->value(),
            'object_id' => $this->resource->objectId->value(),
            'name'=> $this->resource->name->value(),
            'category'=> $this->resource->category->value(),
            'object_code'=> $this->resource->serviceCode->value(),
            'country_in_iso'=> $this->resource->countryInIso->value(),
            'country_in_name'=> $this->resource->countryInName->value(),
            'city_in_iso'=> $this->resource->cityInIso->value(),
            'city_in_name'=> $this->resource->cityInName->value(),
            'zone_in_iso' => $this->resource->zoneInIso->value(),
            'country_out_iso'=> $this->resource->countryOutIso->value(),
            'country_out_name'=> $this->resource->countryOutName->value(),
            'city_out_iso'=> $this->resource->cityOutIso->value(),
            'city_out_name'=> $this->resource->cityOutName->value(),
            'zone_out_iso' => $this->resource->zoneOutIso->value(),
            'start_time'=> $this->resource->startTime->value(),
            'departure_time'=> $this->resource->departureTime->value(),
            'date_in'=> $this->resource->dateIn->value(),
            'date_out'=> $this->resource->dateOut->value(),
            'adults' => $this->resource->totalAdults->value(),
            'children' => $this->resource->totalChildren->value(),
            'infants' => $this->resource->totalInfants->value(),
            'markup_created'=> $this->resource->markupCreated->value(),
            'total_amount'=> $this->resource->totalAmount->value(),
            'serial_sharing'=> $this->resource->serialSharing->value(),
            'flights'=> FileItineraryResource::collection(
                $this->flights->jsonSerialize()
            ),
            'descriptions'=> FileItineraryDescriptionResource::collection(
                $this->descriptions->jsonSerialize()
            ),
            'total_cost_amount' => $this->resource->totalCostAmount->value(),
            'profitability' => $this->resource->profitability->value(),                 
            'service_amount_logs' => FileItineraryServiceAmountLogResource::collection(
                $this->serviceAmountLogs->jsonSerialize()
            ),
            'room_amount_logs' => FileItineraryRoomAmountLogResource::collection(
                $this->roomAmountLogs->jsonSerialize()
            ),
            'rooms'=> FileHotelRoomResource::collection($this->rooms->jsonSerialize()),
            'services' => FileServiceResource::collection($this->services->jsonSerialize()),
        ];
    }


}
