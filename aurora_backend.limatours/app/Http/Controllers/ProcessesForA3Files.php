<?php

namespace App\Http\Controllers;

use App\ReservationsHotelsRatesPlansRooms; 
use Illuminate\Http\Request;

class ProcessesForA3Files extends Controller
{    

    public function channel_code_hyperguest(Request $request)
    {     
        $reservations = [];
        if(isset($request->reservations_rates_plans_rooms_id) and is_array($request->reservations_rates_plans_rooms_id))
        {
            $reservations = ReservationsHotelsRatesPlansRooms::select('id','channel_reservation_code_master')->whereIn('id' , $request->reservations_rates_plans_rooms_id)->get();  
        }
        return response()->json($reservations);

    }

}
