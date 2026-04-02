<?php

namespace App\Http\Resources\Reservation;

use App\Http\Resources\Reservation\ReservationServicesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationDetailsResource extends JsonResource
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
            "id" => $this["id"],
            "executive" => [
                "code" =>  $this["executive"]["code"],
                "name" =>  $this["executive"]["name"]
            ],
            "file_code" => $this["file_code"],            
            "date_ini" => $this["date_init"],
            "created_at" => $this["created_at"],
            "total" => number_format($this["total_amount"],2), 
            'reservation_services' => ReservationServicesResource::collection($this['reservationsService']),
            'reservation_hotels' => ReservationHotelsResource::collection($this['reservationsHotel']), 
            'reservations_flight' => ReservationFlightResource::collection($this['reservationsFlight']), 
            'passengers' => ReservationPassengersResource::collection($this['reservationsPassenger'])
        ];
    }


}
