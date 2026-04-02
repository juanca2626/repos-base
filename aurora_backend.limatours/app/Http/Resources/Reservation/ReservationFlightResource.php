<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationFlightResource extends JsonResource
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
            "code_flight" => $this["code_flight"],
            "origin" => $this["origin"],
            "destiny" => $this["destiny"],              
            "adult_num" => $this["adult_num"],
            "child_num" => $this["child_num"],
            "created_at" => date_format($this["created_at"],"Y-m-d H:i")
        ];

    }
}
