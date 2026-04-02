<?php

namespace Src\Modules\File\Presentation\Http\Resources\Cancelation;

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
            'id' => $this->resource->id,
            'file_hotel_room_id' => $this->resource->fileHotelRoomId->value(),                        
            'confirmation_code' => $this->resource->confirmationCode->value(),
            'amount_sale' => $this->resource->amountSale->value(),
            'amount_cost' => $this->resource->amountCost->value(),
            'taxed_unit_sale' => $this->resource->taxedUnitSale->value(),
            'taxed_unit_cost' => $this->resource->taxedUnitCost->value(),
            'status' => $this->resource->status->value(),
            'confirmation_status' => $this->resource->confirmationStatus->value(), 
            'penality' =>  $this->getPenalty()            
        ];
    }


}
