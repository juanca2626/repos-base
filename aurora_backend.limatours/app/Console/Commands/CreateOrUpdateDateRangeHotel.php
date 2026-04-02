<?php

namespace App\Console\Commands;

use App\DateRangeHotel;
use App\PoliciesRates;
use App\Rates;
use App\RatesPlansCalendarys;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrUpdateDateRangeHotel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:update_date_range';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creacion o actualizacion de rangos de fecha de tarifas de hoteles ';

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
        // se elimino este proceso
    }


    public function proceso_eliminado()
    {
        try {
            $date_range_hotel = DateRangeHotel::where('updated', 1)->first();
            $date_range_hotel_old = null;
            if ($date_range_hotel != null){

                $date_from = Carbon::parse($date_range_hotel->date_from);
                $date_to = Carbon::parse($date_range_hotel->date_to);

                if ($date_range_hotel->old_id_date_range != null) {
                    $date_range_hotel_old = DateRangeHotel::find($date_range_hotel->old_id_date_range);
                    if($date_range_hotel_old){
                        $policy_rate_old =PoliciesRates::find($date_range_hotel_old->policy_id);
                        if($policy_rate_old){
                            $dates_old = $this->getArrayDates(Carbon::parse($date_range_hotel_old->date_from),Carbon::parse($date_range_hotel_old->date_to), $policy_rate_old->days_apply);
                            if($dates_old){
                                $rate_plan_calendar_old_ids =RatesPlansCalendarys::whereIn('date',$dates_old)->where('policies_rate_id',$date_range_hotel_old->policy_id)
                                    ->where('rates_plans_room_id',$date_range_hotel_old->rate_plan_room_id)
                                    ->withTrashed()
                                    ->pluck('id')->toArray();

                                DB::raw("SET SQL_SAFE_UPDATES = 0;");
                                Rates::whereIn('rates_plans_calendarys_id',$rate_plan_calendar_old_ids)->forceDelete();

                                RatesPlansCalendarys::whereIn('date',$dates_old)->where('policies_rate_id',$date_range_hotel_old->policy_id)->where('rates_plans_room_id',$date_range_hotel_old->rate_plan_room_id)->forceDelete();

                                DateRangeHotel::where('id',$date_range_hotel_old->id)->forceDelete();

                                DB::raw("SET SQL_SAFE_UPDATES = 1;");
                            }

                        }
                    }

                    $date_range_hotel_new = DateRangeHotel::find($date_range_hotel->id);
                    $date_range_hotel_new->old_id_date_range = null;
                    $date_range_hotel_new->save();

                }
                else {

                    $policy_rate = PoliciesRates::find($date_range_hotel->policy_id);
                    if($policy_rate){
                        $dates = $this->getArrayDates($date_from, $date_to, $policy_rate->days_apply);
                        DB::raw("SET SQL_SAFE_UPDATES = 0;");
                        $rate_plan_room_calendars = RatesPlansCalendarys::whereIn('date',$dates)->where('policies_rate_id',$date_range_hotel->policy_id)
                            ->withTrashed()
                            ->where('rates_plans_room_id',$date_range_hotel->rate_plan_room_id)
                            ->pluck('id')
                            ->toArray();

                        Rates::whereIn('rates_plans_calendarys_id',$rate_plan_room_calendars)->forceDelete();

                        RatesPlansCalendarys::whereIn('date',$dates)->where('policies_rate_id',$date_range_hotel->policy_id)
                            ->where('rates_plans_room_id',$date_range_hotel->rate_plan_room_id)->forceDelete();

                        DB::raw("SET SQL_SAFE_UPDATES = 1;");

                        foreach ($dates as $date) {
                            $this->saveCalendar($date, $date_range_hotel->policy_id, $date_range_hotel->rate_plan_room_id,
                                $date_range_hotel->price_adult, $date_range_hotel->price_child, $date_range_hotel->price_infant,
                                $date_range_hotel->price_extra);
                        }

                        $date_range_hotel_updated = DateRangeHotel::find($date_range_hotel->id);
                        $date_range_hotel_updated->updated = 0;
                        $date_range_hotel_updated->save();
                    }

                }

            }
        }catch (\Exception $e){

        }
    }

    /**
     * @param Carbon $date_from
     * @param Carbon $date_to
     * @param  $days_apply
     * @return array
     */
    private function getArrayDates(Carbon $date_from, Carbon $date_to, $days_apply)
    {
        $array_dates = [];
        if ($days_apply == "all") {
            while ($date_from <= $date_to) {
                array_push($array_dates, $date_from->format('Y-m-d'));
                $date_from->addDays(1);
            }
        }else{
            $days_of_week_valid = explode('|',$days_apply);
            while ($date_from <= $date_to) {
                if (in_array($date_from->dayOfWeekIso,$days_of_week_valid))
                {
                    array_push($array_dates, $date_from->format('Y-m-d'));

                }
                $date_from->addDays(1);
            }
        }
        return $array_dates;
    }

    /**
     * @param $date
     * @param $policy_id
     * @param $rate_plan_room_id
     * @param $price_adult
     * @param $price_child
     * @param $price_infant
     * @param $price_extra
     */
    private function saveCalendar($date, $policy_id, $rate_plan_room_id, $price_adult, $price_child, $price_infant, $price_extra)
    {
        $calendar = new RatesPlansCalendarys();
        $calendar->date = $date;
        $calendar->policies_rate_id = $policy_id;
        $calendar->rates_plans_room_id = $rate_plan_room_id;
        $calendar->max_ab_offset = null;
        $calendar->min_ab_offset = null;
        $calendar->min_length_stay = null;
        $calendar->max_length_stay = null;
        $calendar->max_occupancy = null;
        $calendar->policies_cancelation_id = null;
        $calendar->save();

        $rate = new Rates();
        $rate->rates_plans_calendarys_id = $calendar->id;
        $rate->num_adult = 0;
        $rate->num_child = 0;
        $rate->num_infant = 0;
        $rate->price_adult = $price_adult;
        $rate->price_child = $price_child;
        $rate->price_infant = $price_infant;
        $rate->price_extra = $price_extra;
        $rate->price_total = 0;
        $rate->save();
    }
}
