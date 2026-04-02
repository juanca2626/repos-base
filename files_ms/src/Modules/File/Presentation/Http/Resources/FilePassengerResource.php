<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TypeRoomDescription;

class FilePassengerResource extends JsonResource
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
            // 'file_id' => $this->resource->fileId->value(),
            'sequence_number' => $this->resource['sequence_number'],
            // 'order_number' => $this->resource->orderNumber->value(),
            // 'frequent'=> $this->resource->frequent->value(),
            'document_type_id'=> $this->resource['document_type_id'],
            'doctype_iso'=> $this->resource['doctype_iso'],
            'document_number'=> $this->resource['document_number'],
            'name'=> $this->resource['name'],
            'surnames'=> $this->resource['surnames'],
            'date_birth'=> $this->resource['date_birth_format'],
            'type'=> strtoupper($this->resource['type']),
            'room_type'=> $this->resource['suggested_room_type'],
            'room_type_description' => (new TypeRoomDescription((int) $this->resource['suggested_room_type']))->toString(), //$this->getRoomTypeDescription(),  //FALTA
            'genre'=> strtoupper($this->resource['genre']),
            'email'=> $this->resource['email'],
            // 'phone'=> $this->resource['phone'],
            'phone_code'=> $this->resource['phone_code'],
            'phone'=> $this->resource['phone_number'],
            'country_iso'=> $this->resource['country_iso'],
            'city_iso'=> $this->resource['city_iso'],
            'dietary_restrictions'=> $this->resource['dietary_restrictions'],
            'medical_restrictions'=> $this->resource['medical_restrictions'],
            // 'notes'=> $this->resource->notes->value(),
            // 'accommodations'=> $this->getAccommodation(),
            // 'cost_by_passenger'=> $this->resource->costByPassenger->value(),
            'document_url'=> $this->resource['document_url'],
        ];
    }


}
