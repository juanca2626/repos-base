<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceAccommodationResource extends JsonResource
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
            'file_service_unit_id' => $this->resource['file_service_unit_id'],
            'file_passenger_id' => $this->resource['file_passenger_id'],
            'room_key' => $this->resource['room_key'],
            'passenger' => new FilePassengerAccommodationResource($this->resource['file_passenger'])
        ];
    }
}
