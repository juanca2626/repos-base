<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationServicesResource extends JsonResource
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
            "code" => $this["service_code"],
            "name" => $this["service_name"],
            "service_type" => $this["type_service"],
            "adult_num" => $this["adult_num"],
            "child_num" => $this["child_num"],
            "date" => $this["date"],
            "created_at" => date_format($this["created_at"],"Y-m-d H:i") , 
            "total" => number_format($this["total_amount"],2) 
        ];
    }
}
