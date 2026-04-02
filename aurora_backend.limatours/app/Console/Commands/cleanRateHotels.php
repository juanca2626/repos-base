<?php

namespace App\Console\Commands;

use App\RatesPlans;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class cleanRateHotels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:rate_hotels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Borrar Tarifas de hoteles eliminadas y relaciones en las tablas';

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
        $rate_plans = DB::table('rates_plans')->whereNotNull('deleted_at')->get();

        foreach ($rate_plans as $rate_plan) {
            $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('rates_plans_id', $rate_plan->id)->pluck('id');

            DB::table('rates_histories')->where('rates_plan_id', $rate_plan->id)->delete();
            DB::table('rates_histories_copy')->where('rates_plan_id', $rate_plan->id)->delete();
            DB::table('rates_plans_promotions')->where('rates_plans_id', $rate_plan->id)->delete();
            DB::table('client_rate_plans')->where('rate_plan_id', $rate_plan->id)->delete();
            DB::table('rate_supplements')->where('rate_plan_id', $rate_plan->id)->delete();

            if (count($rate_plan_room_ids) > 0) {
                DB::table('rates_plans_calendarys')->whereIn('rates_plans_room_id', $rate_plan_room_ids)->orderBy('created_at', 'asc')->chunk(300, function ($calendars) {

                    DB::table('rates')->whereIn('rates_plans_calendarys_id', $calendars->pluck('id')->toArray())->delete();
                    DB::table('rates_plans_calendarys')->whereIn('id', $calendars->pluck('id')->toArray())->delete();

                });
                DB::table('package_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
                DB::table('package_service_optional_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
                DB::table('quote_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
                DB::table('policies_cancelations_rates_plans_rooms')->whereIn('rates_plans_rooms_id', $rate_plan_room_ids)->delete();
                DB::table('inventories')->whereIn('rate_plan_rooms_id', $rate_plan_room_ids)->delete();
                DB::table('bag_rates')->whereIn('rate_plan_rooms_id', $rate_plan_room_ids)->delete();
            }
            DB::table('rates_plans_rooms')->whereIn('id', $rate_plan_room_ids)->delete();
            DB::table('rates_plans')->where('id',$rate_plan->id)->delete();
        }
    }
}
