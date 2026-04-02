<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileHotelRoomUnitNightResource extends JsonResource
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
            'file_hotel_room_unit_id' => $this->resource['file_hotel_room_unit_id'],
            'date' => $this->resource['date'],
            'number' => $this->resource['number'],
            'price_adult_sale' => $this->resource['price_adult_sale'],
            'price_adult_cost' => $this->resource['price_adult_cost'],
            'price_child_sale' => $this->resource['price_child_sale'],
            'price_child_cost' => $this->resource['price_child_cost'],
            'price_infant_sale' => $this->resource['price_infant_sale'],
            'price_infant_cost' => $this->resource['price_infant_cost'],
            'price_extra_sale' => $this->resource['price_extra_sale'],
            'price_extra_cost' => $this->resource['price_extra_cost'],
            'total_amount_sale' => $this->resource['total_amount_sale'],
            'total_amount_cost' => $this->resource['total_amount_cost']
        ];
    }
}
