<?php

namespace App\Http\Controllers;

use App\RatesPlans;
use App\RatesPlansRooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class RatesPlansController extends Controller
{
    public function search(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $hotel_id = $request->input('hotel_id');
        $room_id = $request->input('room_id');

        $rates_frontend = [];

        $rate_plan_rooms = RatesPlansRooms::whereHas('rate_plan', function ($query) use ($hotel_id) {
            $query->where('allotment', 1);
            $query->where('hotel_id', $hotel_id);
        })
        ->where(function($q) {
            $q->where('bag', 0)
              ->orWhere(function($sq) {
                  $sq->where('bag', 1)
                     ->whereDoesntHave('bag_rates', function($ssq) {
                         $ssq->whereHas('bag_room', function($asq) {
                             $asq->whereHas('bag', function($bsq) {
                                 $bsq->where('status', 1);
                             });
                         });
                     });
              });
        })
        ->with('rate_plan')
        ->where('room_id',$room_id)
        ->where('channel_id', 1)
        ->where('status',1);

        if ($querySearch) {
            $rate_plan_rooms->whereHas('rate_plan', function ($query) use ($querySearch) {
                $query->where('name', 'like', '%' . $querySearch . '%');
            });
        }

        $count = $rate_plan_rooms->count();


        if ($paging === 1) {
            $rate_plan_rooms = $rate_plan_rooms->take($limit)->get();
        } else {
            $rate_plan_rooms = $rate_plan_rooms->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($i = 0; $i < $rate_plan_rooms->count(); $i++) {
                $rates_frontend[$i]["bag_rate_id"] = "";
                $rates_frontend[$i]["bag_id"] = "";
                $rates_frontend[$i]["rate_plan_rooms_id"] = $rate_plan_rooms[$i]["id"];
                $rates_frontend[$i]["rate_plan_id"] = $rate_plan_rooms[$i]["rate_plan"]["id"];
                $rates_frontend[$i]["rate_plan_status"] = $rate_plan_rooms[$i]["rate_plan"]["status"];
                $rates_frontend[$i]["name"] = $rate_plan_rooms[$i]["rate_plan"]["name"];
                $rates_frontend[$i]["selected"] = false;
            }
        }

        $data = [
            'data' => $rates_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function ratesByHotel(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $period = $request->input('period');

        $rates = RatesPlans::select('id', 'name')
            ->where('hotel_id', $hotel_id)
            ->where('status',1)
            ->with([
                'clients_rate_plan' => function ($query) use ($period) {
                    $query->where('period', $period);
                }
            ])->get();

        $rates_frontend = [];

        for ($i = 0; $i < $rates->count(); $i++) {
            $rates_frontend[$i]["rate_plan_id"] = $rates[$i]["id"];
            $rates_frontend[$i]["name"] = $rates[$i]["name"];
            $rates_frontend[$i]["selected"] = false;
            $rates_frontend[$i]["clients_rate_plan"] = $rates[$i]["clients_rate_plan"];
        }
        $data = [
            'data' => $rates_frontend,
            'success' => true
        ];

        return Response::json($data);
    }

    public function storeClientRatePlan(Request $request)
    {
        $hotel_clients = $request->input('hotel_clients');

        $rate_plan_id = $request->input('rate_plan_id');

        $period = $request->input('period');

        date_default_timezone_set("America/Lima");



        DB::transaction(function () use ($hotel_clients, $rate_plan_id, $period) {

            foreach ($hotel_clients as $client) {

                if ($client["selected"] && $client["client_rate_plan_id"] === null) {
                    DB::table('markup_rate_plans')->insert([
                        'rate_plan_id' => $rate_plan_id,
                        'client_id' => $client["id"],
                        'markup' => $client["markup"],
                        'period' => $period,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }
                if ($client["selected"] && $client["client_rate_plan_id"] !== "") {
                    DB::table('markup_rate_plans')
                        ->where('id', $client["client_rate_plan_id"])
                        ->update([
                            'markup' => $client["markup"],
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                }
                if (!$client["selected"] && $client["client_rate_plan_id"] !== "") {
                    DB::table('markup_rate_plans')->where('id', '=', $client["client_rate_plan_id"])->delete();
                }
            }
        });

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function  ratesPlansByHotel(Request $request)
    {
        $hotel_id = $request->input('hotel_id');

        $rates_plans = RatesPlans::where('hotel_id',$hotel_id)->where('status',1)->get();

        $data = [
            'data' => $rates_plans,
            'success' => true
        ];

        return Response::json($data);
    }

    public function  ratesPlansByChannel(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $channel_id = $request->input('channel_id');

        $rates_plans = RatesPlans::whereHas('rate_plans_rooms', function ($query) use ($channel_id) {
            $query->where('channel_id', $channel_id);
        })->where('hotel_id',$hotel_id)->where('status',1)->get();

        $data = [
            'data' => $rates_plans,
            'success' => true
        ];

        return Response::json($data);
    }

}
