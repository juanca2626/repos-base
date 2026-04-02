<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryFlightAccommodationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'file_itinerary_flight_id' => $this->resource['file_itinerary_flight_id'],
            'file_passenger_id' => $this->resource['file_passenger_id'],  
            'file_passenger' => isset($this->resource['file_passenger']) ? new FilePassengerAccommodationResource($this->resource['file_passenger']) : []
        ];
    }
}
