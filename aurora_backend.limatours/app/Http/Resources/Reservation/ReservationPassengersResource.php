<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationPassengersResource extends JsonResource
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
            "doctype" => $this["doctype_iso"],
            "name" => $this["name"],
            "surnames" => $this["surnames"],
            "date_birth" => $this["date_birth"],              
            "type" => $this["type"],
            "genre" => $this["genre"],
            "email" => $this["email"],
            "phone" => $this["phone"],
            "country" => $this["country_iso"],
            "city" => $this["city_iso"],
            "dietary_restrictions" => $this["dietary_restrictions"],
            "medical_restrictions" => $this["medical_restrictions"], 
            "created_at" => date_format($this["created_at"],"Y-m-d H:i")
        ];

    }
}
