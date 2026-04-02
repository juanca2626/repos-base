<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileHotelRoomUnitResource extends JsonResource
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
            'file_hotel_room_id' => $this->resource['file_hotel_room_id'],
            'confirmation_code' => $this->resource['confirmation_code'],
            'amount_sale' => $this->resource['amount_sale'],
            'amount_cost' => $this->resource['amount_cost'],
            'taxed_unit_sale' => $this->resource['taxed_unit_sale'],
            'taxed_unit_cost' => $this->resource['taxed_unit_cost'],
            'adult_num' => $this->resource['adult_num'],
            'child_num' => $this->resource['child_num'],
            'status' => $this->resource['status'],
            'confirmation_status' => $this->resource['confirmation_status'],
            'policies_cancellation' =>  $this->resource['policies_cancellation'],
            'penality' =>  $this->resource['penalty'],
            'nights' => FileHotelRoomUnitNightResource::collection($this->resource['nights']),
            'accommodations' => FileHotelRoomUnitAccommodationResource::collection(
                $this->resource['accommodations']
            )
        ];
    }


}
