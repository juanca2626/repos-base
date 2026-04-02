<?php

namespace App\Http\Controllers;

use App\Bag;
use App\BagRate;
use App\BagRoom;
use App\RatesPlansRooms;
use App\ReservationsHotelsRatesPlansRooms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\InventoryBag;

class BagsController extends Controller
{  public function __construct()
    {
        $this->middleware('permission:bags.read')->only('getBag');
        $this->middleware('permission:bags.create')->only('store');
        $this->middleware('permission:bags.update')->only('update');
        $this->middleware('permission:bags.delete')->only('deleteBag');

    }

    public function getBag($id)
    {
        $bag = Bag::find($id);

        return Response::json(['success' => true, 'bag' => $bag]);
    }

    public function getBags(Request $request)
    {
        //Todo: Refactorizar Bloque de pagination
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $sorting = $request->input('orderBy');
        $sortOrder = $request->input('ascending');
        $hotel_id = $request->input('hotel_id');

        $bags = Bag::select('id', 'name', 'status')->where('hotel_id', $hotel_id);

        $count = $bags->count();

        if ($querySearch) {
            $bags->where('name', 'like', '%' . $querySearch . '%');
        }
        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $bags->orderBy($sorting, $asc);
        } else {
            $bags->orderBy('created_at', 'desc');
        }

        if ($paging == 1) {
            $bags = $bags->take($limit)->get();
        } else {
            $bags = $bags->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $bags,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function getBagRates(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $room_id = $request->input('room_id');
        $bag_id = $request->input('bag_id');



        $bag_rates_frontend = [];

        $bag_rates = BagRate::select('id', 'bag_room_id', 'rate_plan_rooms_id')
            ->with('rate_plan_room.rate_plan');
        $bag_rates->whereHas('rate_plan_room', function ($query) use ($querySearch,$room_id) {
            $query->where('room_id',$room_id);
        });
        $bag_rates->whereHas('bag_room', function ($query) use ($querySearch,$bag_id) {
            $query->where('bag_id',$bag_id);
        });

        if ($querySearch) {
            $bag_rates->whereHas('rate_plan_room', function ($query) use ($querySearch) {
                $query->whereHas('rate_plan', function ($query) use ($querySearch) {

                    $query->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }

        $count = $bag_rates->count();

        if ($paging === 1) {
            $bag_rates = $bag_rates->take($limit)->get();
        } else {
            $bag_rates = $bag_rates->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $bag_rates_frontend[$i]["bag_rate_id"] = $bag_rates[$i]["id"];
                $bag_rates_frontend[$i]["bag_room_id"] = $bag_rates[$i]["bag_room_id"];
                $bag_rates_frontend[$i]["rate_plan_rooms_id"] = $bag_rates[$i]["rate_plan_rooms_id"];
                $bag_rates_frontend[$i]["name"] = $bag_rates[$i]["rate_plan_room"]["rate_plan"]["name"];
                $bag_rates_frontend[$i]["rate_plan_id"] = $bag_rates[$i]["rate_plan_room"]["rate_plan"]["id"];
                $bag_rates_frontend[$i]["rate_plan_status"] = $bag_rates[$i]["rate_plan_room"]["rate_plan"]["status"];
                $bag_rates_frontend[$i]["selected"] = false;
            }
        }

        $data = [
            'data' => $bag_rates_frontend,
            'count' => $count,
            'success' => true
        ];
        return Response::json($data);
    }

    public function store(Request $request)
    {
        $bag_frontend = $request->input("bag");
        $bag = new Bag();
        $bag->name = $bag_frontend["name"];
        $bag->status = $bag_frontend["status"];
        $bag->hotel_id = $request->input("hotel_id");
        $bag->save();

        return Response::json(['success' => true, 'bag_id' => $bag->id]);
    }

    public function update(Request $request)
    {
        $bag = Bag::find($request->input('id'));
        $bag->name = $request->input("name");
        $bag->status = $request->input("status");
        $bag->save();

        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $bag = Bag::find($id);
        if ($request->input("status")) {
            $bag->status = false;
        } else {
            $bag->status = true;
        }
        $bag->save();

        return Response::json(['success' => true]);
    }

    public function storeRate(Request $request)
    {

        $bag_id = $request->input("bag_id");
        $rate = $request->input("rate");
        $room_id = $request->input("room_id");

        $bag_room = BagRoom::updateOrCreate(
            [
                'bag_id' => $bag_id,
                'room_id' => $room_id
            ]
        );

        $bag_rate = new BagRate();
        $bag_rate->bag_room_id = $bag_room->id;
        $bag_rate->rate_plan_rooms_id = $rate["rate_plan_rooms_id"];
        $bag_rate->save();


        $rate_exists = RatesPlansRooms::find($rate["rate_plan_rooms_id"]);
        $rate_exists->bag = 1;
        $rate_exists->save();

        return Response::json(['success' => true]);
    }

    public function storeRates(Request $request)
    {
        $bag_id   = $request->input("bag_id");
        $hotel_id = $request->input("hotel_id");
        $room_id  = $request->input("room_id");

        $rates_database = RatesPlansRooms::whereHas('rate_plan', function ($query) use ($hotel_id) {
            $query->where('allotment', 0);
            $query->where('hotel_id', $hotel_id);
        })->where('bag', 0)->where('room_id', $room_id)->where('channel_id', 1)->get();

        date_default_timezone_set("America/Lima");

        $bag_room = BagRoom::updateOrCreate(
            [
                'bag_id' => $bag_id,
                'room_id' => $room_id
            ]
        );

        $bag_room_id = $bag_room->id;

        DB::transaction(function () use ($rates_database, $bag_room_id) {

            foreach ($rates_database as $rate) {
                DB::table('bag_rates')->insert([
                    'bag_room_id' => $bag_room_id,
                    'rate_plan_rooms_id' => $rate["id"],
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                DB::table('rates_plans_rooms')
                    ->where('id', $rate["id"])
                    ->update(['bag' => 1]);
            }
        });
        return Response::json(['success' => true]);
    }

    public function inverseRate(Request $request)
    {

        $rate = $request->input("rate");

        $bag_rate = BagRate::find($rate["bag_rate_id"]);

        $bag_rate->delete();

        $rate_exists = RatesPlansRooms::find($rate["rate_plan_rooms_id"]);
        $rate_exists->bag = 0;
        $rate_exists->save();

        $count = BagRate::where('bag_room_id',$rate["bag_room_id"])->count();

        if ($count === 0)
        {
            InventoryBag::where('bag_room_id',$rate["bag_room_id"])->delete();

            $bag_room = BagRoom::find($rate["bag_room_id"]);
            $bag_room->delete();


        }

        return Response::json(['success' => true]);
    }

    public function inverseRates(Request $request)
    {
        $bag_id = $request->input("bag_id");

        $room_id = $request->input("room_id");

        $bag_room = BagRoom::select('id')->where('room_id',$room_id)->where('bag_id',$bag_id)->first();

        $bag_room_id = $bag_room->id;

        $bag_rates_database = BagRate::select('id', 'bag_room_id', 'rate_plan_rooms_id')
            ->where('bag_room_id', $bag_room_id)->with('rate_plan_room.rate_plan')->get();

        DB::transaction(function () use ($bag_rates_database, $bag_room_id) {

            foreach ($bag_rates_database as $rate) {
                DB::table('bag_rates')->where('bag_room_id', '=', $bag_room_id)->delete();
                DB::table('inventory_bags')->where('bag_room_id', '=', $bag_room_id)->delete();
                DB::table('bag_rooms')->where('id', '=', $bag_room_id)->delete();
                DB::table('rates_plans_rooms')
                    ->where('id', $rate["rate_plan_room"]["id"])
                    ->update(['bag' => 0]);
            }
        });
        return Response::json(['success' => true]);
    }

    public function deleteBag($id)
    {
        $bag = Bag::findOrFail($id);

        DB::transaction(function () use ($bag, $id) {
            // 1. Get all bag_rooms associated with this bag
            $bagRooms = BagRoom::where('bag_id', $id)->get();
            $bagRoomIds = $bagRooms->pluck('id');

            // 2. Update rates_plans_rooms: set bag = 0 for all rates in this bag
            // We find these through bag_rates -> bag_rooms
            $ratePlanRoomIds = BagRate::whereIn('bag_room_id', $bagRoomIds)
                ->pluck('rate_plan_rooms_id');

            if ($ratePlanRoomIds->isNotEmpty()) {
                RatesPlansRooms::whereIn('id', $ratePlanRoomIds)
                    ->update(['bag' => 0]);
            }

            // 3. Delete related data
            // Delete bag_rates
            BagRate::whereIn('bag_room_id', $bagRoomIds)->delete();

            // Delete inventory_bags
            InventoryBag::whereIn('bag_room_id', $bagRoomIds)->delete();

            // Delete bag_rooms
            BagRoom::where('bag_id', $id)->delete();

            // 4. Finally delete the bag
            $bag->delete();
        });

        return Response::json(['success' => true]);
    }

    public function updateReservatiosRate(){

        DB::transaction(function ()  {

            $invatarios = InventoryBag::where('date' ,'>', '2020-01-30')->get()->toArray();
            $i=1;
            foreach($invatarios as $invatario){

                //  if($i == 20){
                //     break;
                //  }

                 $dia = substr('00'.$invatario['day'],-2);
                 $fecha = explode("-",$invatario['date']);
                 $fechaNueva = $fecha[0].'-'.$fecha[1].'-'.$dia;

                 DB::table('inventory_bags')
                 ->where('id', $invatario["id"])
                 ->update(['date' => $fechaNueva]);

                //  print_r($invatario);
                //  echo $fechaNueva."@".$fechaNueva."<br>";
                //  $i++;

            }

        });

        die('fin');

        // $reservation = new \App\Reservation();
        // $reservation->reservations_active_count();

        $reservation = new \App\Reservation();
        $reservation->reservation_canceled_count();


        die('..');

        $rates = ReservationsHotelsRatesPlansRooms::get();
        foreach($rates as $rate){
            $data = json_decode($rate->policies_cancellation);
            $rate->first_penalty_date = Carbon::parse($data[0]->apply_date)->format('Y-m-d');
            $rate->save();
        }
    }

    public function updateRoomsBag(Request $request)
    {
        $rooms = $request->input('rooms');
        $rate_plan_id = $request->input('rate_plan_id');
        $hotel_id = $request->input('hotel_id');

       DB::transaction(function () use ($rooms,$rate_plan_id,$hotel_id) {
            foreach ($rooms as $room) {
                if ($room["adult"] != "" && $room["adult"] > 0) {

                    $rate_plan_room = RatesPlansRooms::where('rates_plans_id', $rate_plan_id)->where('room_id', $room["id"])->where('channel_id',1)->first();

                    if ($rate_plan_room == null) {
                        $rate_plan_room = RatesPlansRooms::create([
                            'rates_plans_id' => $rate_plan_id,
                            'room_id' => $room["id"],
                            'status' => 1,
                            'bag' => 1,
                            'channel_id' => 1
                        ]);
                    }else{

                        $rate_plan_room->bag = 1;
                        $rate_plan_room->save();

                    }

                    $bag = Bag::where('hotel_id', $hotel_id)->first();

                    $bag_room = BagRoom::where('bag_id', $bag->id)->where('room_id', $room["id"])->first();

                    if ($bag_room == null) {
                        $bag_room = BagRoom::create([
                            'bag_id' => $bag->id,
                            'room_id' => $room["id"]
                        ]);
                    }

                    $bag_rate = BagRate::where('bag_room_id', $bag_room->id)->where('rate_plan_rooms_id', $rate_plan_room->id)->first();

                    if ($bag_rate == null) {
                        BagRate::create([
                            'bag_room_id' => $bag_room->id,
                            'rate_plan_rooms_id' => $rate_plan_room->id
                        ]);
                    }
                }
            }
        });

        return \response()->json("habitaciones agregadas a la bolsa");
     }
    public function updateStatusByRates(Request $request)
    {
        $bag = $request->input('bag');
        $status = $request->input('status');
        $room_id = $request->input('room_id');
        $rate_plans_ids = $request->input('rates_plans_ids');

        $data_update = [];
        $data_update['bag'] = $bag;

        if (isset($status)) {
            $data_update['status'] = $status;
        }

        $query = RatesPlansRooms::whereIn('rates_plans_id', $rate_plans_ids);

        if (isset($room_id)) {
            $query->where('room_id', $room_id);
        }

        $query->update($data_update);

        return Response::json(['success' => true]);
    }
}
