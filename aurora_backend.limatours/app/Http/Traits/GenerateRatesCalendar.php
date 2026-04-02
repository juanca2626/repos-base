<?php

namespace App\Http\Traits;

use App\DateRangeHotel;
use App\GenerateRatesInCalendar;
use App\RatesPlans;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

trait GenerateRatesCalendar
{

    public function generateProcessRates($params)
    {

        try {

            $params['status'] = 1;
            $params['user_add'] = auth()->user()->id;

            $generated = GenerateRatesInCalendar::where('hotel_id', $params['hotel_id'])->where('status', '1')->get();
            // $generated = GenerateRatesInCalendar::where('hotel_id',$params['hotel_id'] )->where('rates_plans_id',$params['rates_plans_id'])->where('status','1')->get();

            if (count($generated) > 0) {
                throw new Exception('Hay un proceso ejecutándose, no puede ejecutar esta acción');
            }

            // proceso por hotel
            if (!$params['rates_plans_id']) {

                $rates_plans = RatesPlans::where('hotel_id', $params['hotel_id'])->where('status', '1');

                foreach ($rates_plans as $rates_plan) {

                    $rangos = $this->generateRates($params['hotel_id'], $rates_plan->id, $params['room_id'], $params['perido']);

                    if (count($rangos['date_range_hotel_duplicate']) > 0) {
                        throw new Exception('Tiene más de un rango de fechas que duplicaran la tarifa');
                    }
                }

            } else {

                // proceso por hotel y tarifa
                $rangos = $this->generateRates($params['hotel_id'], $params['rates_plans_id'], $params['room_id'], $params['perido']);

                if (count($rangos['date_range_hotel_duplicate']) > 0) {
                    throw new Exception('Tiene más de un rango de fechas que duplicaran la tarifa');
                }

            }


            $generate_rates_in_calendar = GenerateRatesInCalendar::create($params);

            return $generate_rates_in_calendar->id;


        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function validateDuplicateDate($rateRoomDate)
    {
        $date_range_hotel_duplicate = [];
        foreach ($rateRoomDate as $room_id => $rateDates) {
            foreach ($rateDates as $date => $datesArray) {
                $uniquePolicies = [];
                foreach ($datesArray as $dateEntry) {
                    $policyKey = $dateEntry['policies_rate_id'] . '_' . $dateEntry['rates']['price_adult'];

                    // Si ya encontramos esta combinación de `policy_id` y `price_adult` en la misma fecha, es un duplicado
                    if (in_array($policyKey, $uniquePolicies)) {
                        if (!in_array($dateEntry['date_range_hotel_id'], $date_range_hotel_duplicate)) {
                            array_push($date_range_hotel_duplicate, $dateEntry['date_range_hotel_id']);
                        }
                    } else {
                        $uniquePolicies[] = $policyKey;
                    }
                }
            }
        }
        return $date_range_hotel_duplicate;
    }


    public function generateRates($hotel_id, $rates_plans_id, $room_id = null, $perido = null)
    {
        $perido = $perido == null ? date('Y') : $perido;
        $dateRangeHotel = DateRangeHotel::with('politics');
        $dateRangeHotel->where('hotel_id', $hotel_id)->where('rate_plan_id', $rates_plans_id)->get();

        if ($room_id != null) {
            $dateRangeHotel->where('room_id', $room_id);
        }

        // $dateRangeHotel->where('flag_migrate','<>', '1'); // validar esta información, hay bastantes registros que tiene flag_migrate=1
        $dateRangeHotel->whereNull('old_id_date_range');
        $dateRangeHotel->whereYear('date_from', '>=', $perido);
        $dateRangeHotel->orderBy('room_id');
        $dateRangeHotel->orderBy('date_from');
        $dateRangeHotel = $dateRangeHotel->get();

        $rateRoomDate = [];
        foreach ($dateRangeHotel as $dateRange) {

            $date_from = Carbon::parse($dateRange->date_from);
            $date_to = Carbon::parse($dateRange->date_to);

            $dates = $this->getArrayDates($date_from, $date_to, $dateRange->politics->days_apply);

            foreach ($dates as $date) {
                $rateRoomDate[$dateRange->room_id][$date][] = [
                    'id' => NULL,
                    'date' => $date,
                    'policies_rate_id' => $dateRange->policy_id,
                    'rates_plans_room_id' => $dateRange->rate_plan_room_id,
                    'max_ab_offset' => null,
                    'min_ab_offset' => null,
                    'min_length_stay' => null,
                    'max_length_stay' => null,
                    'max_occupancy' => null,
                    'policies_cancelation_id' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'date_range_hotel_id' => $dateRange->id,
                    'rates' => [
                        'rates_plans_calendarys_id' => NULL,
                        'num_adult' => 0,
                        'num_child' => 0,
                        'num_infant' => 0,
                        'price_adult' => $dateRange->price_adult,
                        'price_child' => $dateRange->price_child,
                        'price_infant' => $dateRange->price_infant,
                        'price_extra' => $dateRange->price_extra,
                        'price_total' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]
                ];
            }
        }

        $date_range_hotel_duplicate = $this->validateDuplicateDate($rateRoomDate);

        return [
            'date_range_hotel_duplicate' => $date_range_hotel_duplicate,
            'rateRoomDate' => $rateRoomDate
        ];

    }

    public function storeRates($rates, $perido, $rates_plans_id = null)
    {

        $insertId = RatesPlansCalendarys::max('id') + 1;

        $data_calendaries = [];
        $data_calendary_rates = [];
        foreach ($rates as $room_id => $rateDates) {
            foreach ($rateDates as $dates) {

                $calendary_rates = $dates[0];
                $rates = $calendary_rates['rates'];

                $calendary_rates['id'] = $insertId;
                $rates['rates_plans_calendarys_id'] = $insertId;

                unset($calendary_rates['date_range_hotel_id']);
                unset($calendary_rates['rates']);

                array_push($data_calendaries, $calendary_rates);
                array_push($data_calendary_rates, $rates);

                $insertId++;
            }
        }


        //Eliminamos todos los rates_plans_calendarys donde el rates_plans_room_id se igual a la tarifa que se esta processando
        $rates_plans_rooms_ids = RatesPlansRooms::select('id')->where('rates_plans_id', $rates_plans_id)->pluck('id')->toArray();
        DB::table('rates_plans_calendarys')->whereIn('rates_plans_room_id', $rates_plans_rooms_ids)->whereYear('date', '>=', $perido)->delete();

        foreach (array_chunk($data_calendaries, 1000) as $data) {
            DB::table('rates_plans_calendarys')->insert($data);
        }

        foreach (array_chunk($data_calendary_rates, 1000) as $data) {
            DB::table('rates')->insert($data);
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
        } else {
            $days_of_week_valid = explode('|', $days_apply);
            while ($date_from <= $date_to) {
                if (in_array($date_from->dayOfWeekIso, $days_of_week_valid)) {
                    array_push($array_dates, $date_from->format('Y-m-d'));

                }
                $date_from->addDays(1);
            }
        }
        return $array_dates;
    }


    public function processRates($generate_rates_in_calendar_id)
    {

        $generate_rates_in_calendar = GenerateRatesInCalendar::find($generate_rates_in_calendar_id);

        $rangos = $this->generateRates($generate_rates_in_calendar->hotel_id, $generate_rates_in_calendar->rates_plans_id, $generate_rates_in_calendar->room_id, $generate_rates_in_calendar->perido);

        if (count($rangos['date_range_hotel_duplicate']) > 0) {
            $generate_rates_in_calendar->status = 3;
            $generate_rates_in_calendar->status_message = 'Tiene más de un rango de fechas que duplicaran la tarifa';
        } else {

            if (count($rangos['rateRoomDate']) == 0) {
                $generate_rates_in_calendar->status = 3;
                $generate_rates_in_calendar->status_message = 'No hay tarifas con rangos de fechas ha procesar';
            } else {
                $this->storeRates($rangos['rateRoomDate'], $generate_rates_in_calendar->perido, $generate_rates_in_calendar->rates_plans_id);
                $generate_rates_in_calendar->status = 2; // ok : se proceso
            }

        }

        $generate_rates_in_calendar->save();

    }

}
