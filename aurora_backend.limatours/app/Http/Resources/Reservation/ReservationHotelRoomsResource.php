<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationHotelRoomsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $ok_rq = "";
        if($this["onRequest"] === 1){
            $ok_rq = "OK"; 
        }else{
            $ok_rq = "RQ";
        }

        $status = "";
        if($this["onRequest"] === 1){
            $status = "Active"; 
        }else{
            $status = "Canceled";
        }

        return [         
            "name" => $this["room_name"],
            "nights" => $this["nights"],
            "adult_num" => $this["adult_num"],
            "child_num" => $this["child_num"],            
            "check_in" => $this["check_in"],
            "check_out" => $this["check_out"], 
            "rate" => $this["rate_plan_name"],
            "ok_rq" => $ok_rq,
            "status" => $status,
            "created_at" => date_format($this["created_at"],"Y-m-d H:i") , 
            "total" => number_format($this["total_amount"],2),
            "price_per_day" => ReservationHotelRoomCalendaryResource::collection($this["reservationsHotelsCalendarys"]),
        ];
    }
}
