<?php

namespace App\Console\Commands;

use App\DateRangeHotel;
use App\RatesHistory;
use App\RatesPlans;
use App\RatesPlansRooms;
use Carbon\Carbon;
use Illuminate\Console\Command;

class updateDateRangesHotels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:date_range_hotels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar rates histories a date ranges hotel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rate_plans = RatesPlans::all();

        foreach ($rate_plans as $rate_plan) {
            $rate_history = RatesHistory::where('rates_plan_id', $rate_plan["id"])->first();

            if ($rate_history!=null)
            {
                if ($rate_history->dataRooms != '' && $rate_history->dataRooms!=null)
                {
                    $rate_history_dataRooms = json_decode($rate_history->dataRooms, true);

                    //get room ids
                    $room_ids = [];
                    foreach ($rate_history_dataRooms as $dataRoom)
                    {
                        if (!in_array($dataRoom["id"], $room_ids)) {

                            array_push($room_ids,$dataRoom["id"]);
                        }
                    }

                    $rate_plan_rooms = RatesPlansRooms::select('id','room_id')->whereIn('room_id',$room_ids)->where('rates_plans_id',$rate_plan["id"])->where('status',1)->where('channel_id',1)->get();

                    //Add field rate_plan_room_id
                    foreach ($rate_history_dataRooms as $index=> $dataRoom){
                        foreach ($rate_plan_rooms as $rate_plan_room){
                            if ($rate_plan_room["room_id"] == $dataRoom["id"])
                            {
                                $rate_history_dataRooms[$index]["rate_plan_room_id"] = $rate_plan_room["id"];
                            }
                        }
                    }

                    //add new date ranges hotel
                    foreach ($rate_history_dataRooms as $dataRoom)
                    {
                        $date_range_hotel = new DateRangeHotel();
                        $date_range_hotel->date_from = Carbon::createFromFormat('d/m/Y', $dataRoom["dates_from"])->format('Y-m-d');
                        $date_range_hotel->date_to = Carbon::createFromFormat('d/m/Y', $dataRoom["dates_to"])->format('Y-m-d');
                        $date_range_hotel->price_adult = ($dataRoom["adult"] == '') ? 0 : $dataRoom["adult"];
                        $date_range_hotel->price_child = ($dataRoom["child"] == '') ? 0 : $dataRoom["child"];
                        $date_range_hotel->price_infant = ($dataRoom["infant"] == '') ? 0 : $dataRoom["infant"];
                        $date_range_hotel->price_extra = ($dataRoom["extra"] == '') ? 0 : $dataRoom["extra"];
                        $date_range_hotel->discount_for_national = 0;
                        $date_range_hotel->rate_plan_id = $rate_plan["id"];
                        $date_range_hotel->hotel_id = $rate_plan["hotel_id"];
                        $date_range_hotel->room_id = $dataRoom["id"];
                        $date_range_hotel->rate_plan_room_id = $dataRoom["rate_plan_room_id"];
                        $date_range_hotel->meal_id = $rate_history["meal_id"];
                        $date_range_hotel->policy_id = $dataRoom["policy_id"];
                        $date_range_hotel->group = $dataRoom["group"];
                        $date_range_hotel->updated = 0;
                        $date_range_hotel->save();
                    }
                }
            }
        }
    }
}
