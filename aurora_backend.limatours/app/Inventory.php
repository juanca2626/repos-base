<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Inventory extends Model
{
    use SoftDeletes;

    static function list($hotel_id,$lang,$allotment,$room_id,$rate_plan_id,$client_id,$year,$month){

        $client_rate_plans = [];
        $client_rate_plans = ClientRatePlan::select('rate_plan_id')->where('client_id', $client_id)->get();

        $inventories = [];

        $rate_plan_rooms = RatesPlansRooms::whereHas('room', function ($query) use ($hotel_id, $room_id) {
            $query->where('hotel_id', $hotel_id);
            $query->where('state', '1');
            $query->where('inventory', 1);
            if ($room_id) {
                $query->where('room_id', $room_id);
            }
        })->with([
            'room.translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->whereHas('rate_plan', function ($query) use ($allotment, $client_rate_plans, $client_id) {
            if ($allotment == 1 && $client_id != "") {
                $query->whereIn('id', $client_rate_plans);
            }
            $query->where('status',1);
            $query->where('allotment', 1);
        })->with('rate_plan')->where('bag', 0)->where('channel_id', 1)->where('status', 1);

        if ($rate_plan_id) {
            $rate_plan_rooms = $rate_plan_rooms->where('rates_plans_id', $rate_plan_id);
        }
        $rate_plan_rooms = $rate_plan_rooms->get();
        $bags = [];

        $room_ids = Room::where('hotel_id', $hotel_id)->where('state', 1)->where('inventory', 1)->pluck('id');
        if ($allotment === 0 && $rate_plan_id == null) {
            $bags = Bag::where('hotel_id', $hotel_id)->where('status', 1)
                ->with([
                    'bag_rooms' => function ($query) use ($hotel_id, $room_id, $room_ids, $lang) {

                        $query->whereIn('room_id', $room_ids);

                        $query->with([
                            'room.translations' => function ($query) use ($lang) {
                                $query->where('type', 'room');
                                $query->whereHas('language', function ($q) use ($lang) {
                                    $q->where('iso', $lang);
                                });
                            }
                        ]);
                    }
                ])->get();

        }

        // $fecha_carbon = Carbon::parse($request->input('year')."-".$request->input('month')."-01");

        if($month){
            $fechaInicio = Carbon::parse($year."-".$month."-01");
            $days = cal_days_in_month(CAL_GREGORIAN, $fechaInicio->month, $fechaInicio->year);
            $fechaFin = Carbon::parse($year."-".$month."-".$days);
            $columns = [];
        }else{
            //por año
            $fechaInicio = Carbon::parse($year."-".date('m')."-01");
            $fechaFin = Carbon::parse($year."-12-31");
            $days = $fechaInicio->diffInDays($fechaFin);
            $days = $days+1;
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $columns = [];
            for ($i = 1; $i <= $days; $i++) {
                $day = date("d/m/Y", strtotime("$fechaInicio + ".($i-1)." days"));
                $date = \DateTime::createFromFormat("d/m/Y", $day);

                $month_format = ucwords(strftime("%B",$date->getTimestamp()));
                $columns[$month_format][] = ['day' => $date->format('d'), 'day_format' => utf8_encode(strftime("%a",$date->getTimestamp()))];


            }

        }


        $inventories_exists = Inventory::whereBetween('date', [
            $fechaInicio->format('Y-m-d'),
            $fechaFin->format('Y-m-d')
        ])->get();

        $inventories_bags_exists = InventoryBag::whereBetween('date', [
            $fechaInicio->format('Y-m-d'),
            $fechaFin->format('Y-m-d')
        ])->get();


        $inventories_exists_group = $inventories_exists->groupBy('rate_plan_rooms_id');
        $inventories_bag_exists_group = $inventories_bags_exists->groupBy('bag_room_id');

        $index_inventory = 0;
        // echo json_encode($inventories_bag_exists_group);
        // die('..');
        if (count($bags) > 0) {



            for ($k = 0; $k < $bags->count(); $k++) {
                if (count($bags[$k]["bag_rooms"]) > 0) {
                    for ($l = 0; $l < count($bags[$k]["bag_rooms"]); $l++) {



                        // if($bags[$k]["bag_rooms"][$l]["id"] != '170')continue;

                        $inventories[$index_inventory] = [
                            "rate_name" => $bags[$k]["bag_rooms"][$l]["room"]["translations"][0]["value"],
                            "bag_name" =>  $bags[$k]["name"],
                            "bag" => $bags[$k]
                        ];
                        for ($m = 1; $m <= $days; $m++) {
                            /*
                            $query = DateRangeHotel::where('date_from', '<=', ($request->input('year')."-".substr('00'.$request->input('month'),
                                    -2)."-".substr('00'.$i, -2)))
                                ->where('date_to', '>=', ($request->input('year')."-".substr('00'.$request->input('month'),
                                        -2)."-".substr('00'.$i, -2)))
                                ->where('rate_plan_id', '=', $rate_plan_rooms[$j]["rate_plan"]["id"])
                                ->where('rate_plan_room_id', '=', $rate_plan_rooms[$j]["id"])->first();
                            */

                            $dateForInventory = date("Y-m-d", strtotime("$fechaInicio + ".($m-1)." days"));

                            $inventory_day_exists = self::checkExistsBag(
                                $inventories_bag_exists_group,
                                $bags[$k]["bag_rooms"][$l]["id"],
                                $dateForInventory
                            );
                            if ($inventory_day_exists === null) {
                                $inventories[$index_inventory]["inventory"][$m] = [
                                    "day" => $m,
                                    "room_id" => $bags[$k]["bag_rooms"][$l]["room_id"],
                                    "bag_room_id" => $bags[$k]["bag_rooms"][$l]["id"],
                                    "rate_plan_rooms_id" => "",
                                    "id" => "",
                                    "date" => $dateForInventory,
                                    "inventory_num" => '',
                                    "selected" => false,
                                    "class_selected" => false,
                                    "locked" => false,
                                    "class_locked" => false,
                                    "class_intermediate" => false,
                                    "class_normal" => true,
                                ];
                            } else {
                                $inventories[$index_inventory]["inventory"][$m] = [
                                    "day" => $inventory_day_exists["day"],
                                    "room_id" => $bags[$k]["bag_rooms"][$l]["room_id"],
                                    "bag_room_id" => $bags[$k]["bag_rooms"][$l]["id"],
                                    "rate_plan_rooms_id" => "",
                                    "id" => $inventory_day_exists["id"],
                                    "date" => $inventory_day_exists["date"],
                                    "inventory_num" => $inventory_day_exists["inventory_num"],
                                    "selected" => false,
                                    "class_selected" => false,
                                    "locked" => $inventory_day_exists["locked"],
                                    "class_locked" => false,
                                    "class_intermediate" => false,
                                    "class_normal" => true,
                                ];
                                if ($inventory_day_exists["locked"] === 1) {
                                    $inventories[$index_inventory]["inventory"][$m]["class_locked"] = true;
                                    $inventories[$index_inventory]["inventory"][$m]["class_normal"] = false;
                                }
                            }

                            /*
                            if(!(isset($query['flag_migrate']) AND $query['flag_migrate'] == 0) AND !$rate_plan_rooms[$j]['rate_plan']['promotions'])
                            {
                                $inventories[$index_inventory]["inventory"][$i]["locked"] = true;
                                $inventories[$index_inventory]["inventory"][$i]["class_locked"] = true;
                                $inventories[$index_inventory]["inventory"][$i]["class_normal"] = false;
                            }
                            */
                        }

                        $index_inventory++;
                    }
                }
            }
        }

        if ($rate_plan_rooms->count() > 0) {



            for ($j = 0; $j < $rate_plan_rooms->count(); $j++) {
                $inventories[$index_inventory] = [
                    "rate_name" => $rate_plan_rooms[$j]["room"]["translations"][0]["value"],
                    "bag_name" => $rate_plan_rooms[$j]["rate_plan"]["name"],
                    "rate_plan_rooms_id" => $rate_plan_rooms[$j]["id"],
                    "rate_plan_rooms" => $rate_plan_rooms[$j],
                ];


                for ($i = 1; $i <= $days; $i++) {
                    // $query = DateRangeHotel::where('date_from', '<=', ($request->input('year')."-".substr('00'.$request->input('month'),
                    //         -2)."-".substr('00'.$i, -2)))
                    //     ->where('date_to', '>=', ($request->input('year')."-".substr('00'.$request->input('month'),
                    //             -2)."-".substr('00'.$i, -2)))
                    //     ->where('rate_plan_id', '=', $rate_plan_rooms[$j]["rate_plan"]["id"])
                    //     ->where('rate_plan_room_id', '=', $rate_plan_rooms[$j]["id"])->first();


                    $dateForInventory = date("Y-m-d", strtotime("$fechaInicio + ".($i-1)." days"));

                    $inventory_day_exists = self::checkExists(
                        $inventories_exists_group,
                        $rate_plan_rooms[$j]["id"],
                        $dateForInventory,  // $i,
                        $rate_plan_rooms[$j]["room_id"]
                    );
                    if ($inventory_day_exists === null) {

                        $inventories[$index_inventory]["inventory"][$i] = [
                            "day" => $i,
                            "bag_room_id" => "",
                            "room_id" => $rate_plan_rooms[$j]["room_id"],
                            "rate_plan_rooms_id" => $rate_plan_rooms[$j]["id"],
                            "id" => "",
                            "date" => $dateForInventory,
                            "inventory_num" => '',
                            "selected" => false,
                            "class_selected" => false,
                            "locked" => false,
                            "class_locked" => false,
                            "class_intermediate" => false,
                            "class_normal" => true,
                        ];

                    } else {
                        $inventories[$index_inventory]["inventory"][$i] = [
                            "day" => $inventory_day_exists["day"],
                            "bag_room_id" => "",
                            "room_id" => $rate_plan_rooms[$j]["room_id"],
                            "rate_plan_rooms_id" => $inventory_day_exists["rate_plan_rooms_id"],
                            "id" => $inventory_day_exists["id"],
                            "date" => $inventory_day_exists["date"],
                            "inventory_num" => $inventory_day_exists["inventory_num"],
                            "selected" => false,
                            "class_selected" => false,
                            "locked" => $inventory_day_exists["locked"],
                            "class_locked" => false,
                            "class_intermediate" => false,
                            "class_normal" => true,
                        ];

                        if ($inventory_day_exists["locked"] === 1) {
                            $inventories[$index_inventory]["inventory"][$i]["class_locked"] = true;
                            $inventories[$index_inventory]["inventory"][$i]["class_normal"] = false;
                        }
                    }

//                    if(!(isset($query['flag_migrate']) AND $query['flag_migrate'] == 0) AND !$rate_plan_rooms[$j]['rate_plan']['promotions'])
//                    {
//                        $inventories[$index_inventory]["inventory"][$i]["locked"] = true;
//                        $inventories[$index_inventory]["inventory"][$i]["class_locked"] = true;
//                        $inventories[$index_inventory]["inventory"][$i]["class_normal"] = false;
//                    }
                }
                $index_inventory++;
            }
        }

        return [
            'bags' => $bags,
            'rate_plan_rooms' => $rate_plan_rooms,
            'success' => true,
            'inventories' => $inventories,
            "days" => $days,
            "columns" => $columns
        ];


    }

    static function checkExists($inventories_exists_group, $rate_plan_rooms_id, $day)
    {
        foreach ($inventories_exists_group as $key => $inventory_of_rate) {
            if ($key == $rate_plan_rooms_id) {
                foreach ($inventory_of_rate as $inventory_day) {
                    if ($inventory_day["date"] == $day) {
                        return $inventory_day;
                    }
                }
            }
        }
    }

    static function checkExistsBag($inventories_bag_exists_group, $bag_room_id, $day)
    {
        foreach ($inventories_bag_exists_group as $key => $inventory_of_rate) {
            if ($key == $bag_room_id) {
                foreach ($inventory_of_rate as $inventory_day) {
                    if ($inventory_day["date"] == $day) {
                        return $inventory_day;
                    }
                }
            }
        }
    }


}
