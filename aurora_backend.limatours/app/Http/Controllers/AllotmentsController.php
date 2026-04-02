<?php

namespace App\Http\Controllers;

use App\Allotment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AllotmentsController extends Controller
{
    public function updateNumDaysBlocked(Request $request)
    {
        $client_id = $request->input('client_id');
        $rate_plan_rooms_id = $request->input('rate_plan_rooms_id');
        $num_days_blocked = $request->input('num_days_blocked');

        $id = Allotment::select('id')->where('client_id',$client_id)->where('rate_plan_rooms_id',$rate_plan_rooms_id)->first();

        if ($id !="")
        {
            $allotment = Allotment::find($id["id"]);
            $allotment->num_days_blocked = $num_days_blocked;
            $allotment->save();
        }else{

            $allotment = new Allotment();
            $allotment->client_id = $client_id;
            $allotment->rate_plan_rooms_id = $rate_plan_rooms_id;
            $allotment->num_days_blocked = $num_days_blocked;
            $allotment->save();
        }

        return Response::json(['success' => true]);
    }
}
