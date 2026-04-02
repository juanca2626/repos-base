<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationHotelsResource extends JsonResource
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
            "code" => $this["hotel_code"],
            "name" => $this["hotel_name"],
            "nights" => $this["nights"],
            "check_in" => $this["check_in"],
            "check_out" => $this["check_out"],  
            "created_at" => date_format($this["created_at"],"Y-m-d H:i") , 
            // "status" => $status,
            "total" => number_format($this["total_amount"],2),
            "rooms" => ReservationHotelRoomsResource::collection($this["reservationsHotelRooms"]),
        ];
    }
}
