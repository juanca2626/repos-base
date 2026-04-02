<?php

namespace Src\Modules\File\Presentation\Http\Resources\Cancelation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource; 

class FileHotelRoomResource extends JsonResource
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
            'room_name' => $this->resource->roomName->value(),
            'room_type' => $this->resource->roomType->value(),
            'rate_plan_id' => $this->resource->ratePlanId->value(),
            'rate_plan_name' => $this->resource->ratePlanName->value(), 
            'rate_plan_code' => $this->resource->ratePlanCode->value(),  
            'channel_id' => $this->resource->channelId->value(),
            'total_rooms' => $this->resource->totalRooms->value(),
            'status' => $this->resource->status->value(),
            'confirmation_status' => $this->resource->confirmationStatus->value(),          
            'total_adults' => $this->resource->totalAdults->value(),
            'total_children' => $this->resource->totalChildren->value(),   
            'amount_sale' => $this->resource->amountSale->value(),
            'amount_cost' => $this->resource->amountCost->value(), 
            'units' => FileHotelRoomUnitResource::collection($this->units->jsonSerialize()),
        ];
    }


}
