<?php

namespace App\Console\Commands;

use App\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class cleanRoomsDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:rooms_disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar habitaciones desactivadas y todas sus dependencias';

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
       $room_disable_ids = DB::table('rooms')->where('state',0)->pluck('id')->toArray();

        $rate_plan_room_ids = DB::table('rates_plans_rooms')->whereIn('room_id', $room_disable_ids)->pluck('id');

        if (count($rate_plan_room_ids) > 0) {

            DB::table('rates_plans_rooms')->whereIn('id', $rate_plan_room_ids)->orderBy('room_id')->chunk(10, function ($rate_plan_rooms) {

                DB::table('date_range_hotels')->whereIn('rate_plan_room_id',$rate_plan_rooms->pluck('id')->toArray())->delete();

                DB::table('rates_plans_calendarys')->whereIn('rates_plans_room_id', $rate_plan_rooms->pluck('id')->toArray())->orderBy('created_at', 'asc')->chunk(300, function ($calendars) {

                    DB::table('rates')->whereIn('rates_plans_calendarys_id', $calendars->pluck('id')->toArray())->delete();
                    DB::table('rates_plans_calendarys')->whereIn('id', $calendars->pluck('id')->toArray())->delete();

                });
                DB::table('package_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_rooms->pluck('id')->toArray())->delete();
                DB::table('package_service_optional_rooms')->whereIn('rate_plan_room_id', $rate_plan_rooms->pluck('id')->toArray())->delete();
                DB::table('quote_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_rooms->pluck('id')->toArray())->delete();
                DB::table('policies_cancelations_rates_plans_rooms')->whereIn('rates_plans_rooms_id', $rate_plan_rooms->pluck('id')->toArray())->delete();
                DB::table('inventories')->whereIn('rate_plan_rooms_id', $rate_plan_rooms->pluck('id')->toArray())->delete();
                DB::table('bag_rates')->whereIn('rate_plan_rooms_id', $rate_plan_rooms->pluck('id')->toArray())->delete();
            });
        }
        DB::table('channel_room')->whereIn('room_id',$room_disable_ids)->delete();
        $bag_room_ids = DB::table('bag_rooms')->whereIn('room_id',$room_disable_ids)->pluck('id');
        DB::table('bag_rates')->whereIn('bag_room_id',$bag_room_ids)->delete();
        DB::table('inventory_bags')->whereIn('bag_room_id',$bag_room_ids)->delete();
        DB::table('bag_rooms')->whereIn('room_id',$room_disable_ids)->delete();
        DB::table('rates_plans_rooms')->whereIn('id', $rate_plan_room_ids)->delete();
        DB::table('rooms')->whereIn('id',$room_disable_ids)->delete();
    }
}
