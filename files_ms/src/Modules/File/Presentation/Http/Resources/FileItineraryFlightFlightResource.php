<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryFlightFlightResource extends JsonResource
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
            'file_itinerary_id' => $this->resource['file_itinerary_id'],
            'airline_name' => $this->resource['airline_name'],
            'airline_code' => $this->resource['airline_code'],
            'airline_number' => $this->resource['airline_number'],
            'departure_time' => $this->resource['departure_time'],
            'arrival_time' => $this->resource['arrival_time'],
            'pnr' => $this->resource['pnr'],
            'nro_pax' => $this->resource['nro_pax'],
            'accommodations' => isset($this->resource['accommodations']) ? FileItineraryFlightAccommodationResource::collection($this->resource['accommodations']):[]
        ];
    }


}
