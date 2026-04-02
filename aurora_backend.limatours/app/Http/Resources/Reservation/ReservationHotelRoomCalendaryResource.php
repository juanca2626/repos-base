<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationHotelRoomCalendaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {      
        return [         
            "date" => $this["date"], 
            "total_amount" => number_format($this["total_amount"],2), 
        ];
    }
}
