<?php

namespace Src\Modules\File\Presentation\Http\Resources;
use Carbon\Carbon; 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryRoomAmountLogResource extends JsonResource
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
            'user_id' => $this->resource['file_room_amount_log']['user_id'], 
            'amount_previous' => $this->resource['file_room_amount_log']['amount_previous'], 
            'amount' => $this->resource['file_room_amount_log']['amount'], 
            'markup' => $this->resource['markup'],
            'date' => Carbon::parse($this->resource['file_room_amount_log']['created_at'])->format('d/m/Y H:i:s')
        ];
    }
}
