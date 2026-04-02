<?php

namespace Src\Modules\File\Presentation\Http\Resources\Cancelation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceCompositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->units);
        // return $this->resource;
        return [
            'id' => $this->resource->id,     
            'code' => $this->resource->code->value(),
            'name' => $this->resource->name->value(),                        
            'start_time' => $this->resource->startTime->value(),
            'departure_time' => $this->resource->departureTime->value(),
            'date_in' => $this->resource->dateIn->value(),
            'date_out' => $this->resource->dateOut->value(),
            'currency' => $this->resource->currency->value(), 
            'total_amount' => $this->resource->totalAmount->value(),             
            'duration_minutes' => $this->resource->durationMinutes->value(),
            'status' => $this->resource->status->value(),
            'units' => FileServiceUnitResource::collection($this->units->jsonSerialize()),   
            
        ];
    }


}

// 'accommodations' => FileHotelRoomUnitAccommodationResource::collection(
//     $this->accommodations->jsonSerialize()
// )  