<?php

namespace App\Http\Controllers;

use App\Client;
use App\ServiceClientRatePlan;
use App\ServiceMarkupRatePlan;
use App\ServiceRate;
use App\ServiceRatePlanCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

// RatesPlansCalendarys
// ClientRatePlan
// RatesPlans;

class ServiceClientRatePlansController extends Controller
{

    public function index(Request $request)
    {
        $client_id = $request->input('client_id');
        $service_id = $request->input('service_id');
        $period = $request->input('period');

        $client_rate_ids = ServiceClientRatePlan::where([
            'client_id' => $client_id,
            'period' => $period
        ])->pluck('service_rate_id');

        $rate_plans = ServiceRate::select('id', 'name')->where('service_id', $service_id)->whereIn('id',
            $client_rate_ids)->get();

        $tarifas = array();
        foreach ($rate_plans as $rate_plan) {
            $rate_plan->selected = false;
            $tarifas[] = $rate_plan;
        }

        return Response::json(['success' => true, 'data' => $tarifas]);
    }

    public function consultSelected(Request $request)
    {
        $service_id = $request->input('service_id');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        $client_rate_ids = ServiceClientRatePlan::where([
            'client_id' => $client_id,
            'period' => $period
        ])->pluck('service_rate_id');

        $rate_plans = ServiceRate::select('id', 'name')->where('service_id', $service_id)->whereNotIn('id',
            $client_rate_ids)->get();

        $markupRates = ServiceMarkupRatePlan::select(['markup', 'service_rate_id'])->where([
            'client_id' => $client_id,
            'period' => $period
        ])->get();

        $tarifas = array();
        foreach ($rate_plans as $rate_plan) {

            $markup = "";
            foreach ($markupRates as $markupRate) {
                if ($markupRate->service_rate_id == $rate_plan->id) {
                    $markup = $markupRate->markup;
                }
            }
            $rate_plan->markup = $markup;
            $rate_plan->selected = false;
            $tarifas[] = $rate_plan;
        }
        return Response::json(['success' => true, 'data' => $tarifas]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $period = $request->input('period');
            $client_id = $request->input('client_id');
            $service_rate_id = $request->input('service_rate_id');

            $rate_plans = new ServiceClientRatePlan();
            $rate_plans->service_rate_id = $service_rate_id;
            $rate_plans->period = $period;
            $rate_plans->client_id = $client_id;
            $rate_plans->save();

            $this->deleteMarkupRatePlans($client_id, $service_rate_id, $period);

        });
        return Response::json(['success' => true]);
    }

    public function storeAll(Request $request)
    {
        DB::transaction(function () use ($request) {
            $service_id = $request->input('service_id');
            $client_id = $request->input('client_id');
            $period = $request->input('period');

            $client_rate_ids = ServiceClientRatePlan::where([
                'client_id' => $client_id,
                'period' => $period
            ])->pluck('service_rate_id');

            $rate_plans = ServiceRate::select('id', 'name')->where('service_id', $service_id)->whereNotIn('id',
                $client_rate_ids)->get();

            foreach ($rate_plans as $rate_plan) {
                $rate_plans = new ServiceClientRatePlan();
                $rate_plans->service_rate_id = $rate_plan->id;
                $rate_plans->period = $period;
                $rate_plans->client_id = $client_id;
                $rate_plans->save();
                $this->deleteMarkupRatePlans($client_id, $rate_plan->id, $period);
            }
        });

        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $client_id = $request->input('client_id');
        $period = $request->input('period');
        $service_rate_plan_id = $request->input('rate_plan_id');
        ServiceClientRatePlan::where([
            'client_id' => $client_id,
            'period' => $period,
            'service_rate_id' => $service_rate_plan_id
        ])->each(function ($rate) {
            $rate->delete();
        });
        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {

        $client_id = $request->input('client_id');
        $service_id = $request->input('service_id');
        $period = $request->input('period');
        ServiceClientRatePlan::whereHas('serviceRate', function ($query) use ($service_id) {
            $query->where('service_id', $service_id);
        })->where('period', $period)->where('client_id', $client_id)->each(function ($rate) {
            $rate->delete();
        });
        return Response::json(['success' => true]);
    }

    public function deleteMarkupRatePlans($client_id, $service_rate_id, $period)
    {
        $ratesMarkup = ServiceMarkupRatePlan::where('client_id', $client_id)->where('service_rate_id',
            $service_rate_id)->where('period', $period)->first();
        if (is_object($ratesMarkup)) {
            $ratesMarkup->delete();
        }
    }


    public function destroy(Request $request)
    {
        $id = $request->input('id');

        ServiceClientRatePlan::where('id', $id)->delete();

        return Response::json(['success' => true,]);
    }

    public function update(Request $request)
    {
        $rate_plan_id = $request->input('rate_plan_id');
        $client_id = $request->input('client_id');
        $markup = $request->input('markup');
        $period = $request->input('period');

        $ratesMarkup = ServiceMarkupRatePlan::where('client_id', $client_id)->where('service_rate_id',
            $rate_plan_id)->where('period', $period)->first();

        if (is_object($ratesMarkup)) {
            $ratesMarkup->markup = $markup;
            $ratesMarkup->save();
        } else {
            $ratesMarkup = new ServiceMarkupRatePlan();
            $ratesMarkup->client_id = $client_id;
            $ratesMarkup->service_rate_id = $rate_plan_id;
            $ratesMarkup->period = $period;
            $ratesMarkup->markup = $markup;
            $ratesMarkup->save();
        }

        return Response::json(['success' => true]);
    }

    public function calendar($service_id, Request $request)
    {
        $lang = $request->input('lang');
        $data = explode('-', $request->input('date'));

        $from = Carbon::createFromDate((int)$data[1], (int)$data[0], 1);
        $to = $from->copy()->endOfMonth();

        $clientRatePlan = ServiceClientRatePlan::where('client_id', $request->input('client'))
            ->get();

        $ratesIDs = $clientRatePlan->pluck('service_rate_id');
        $ratesMarkups = $clientRatePlan->keyBy('client_id')->toArray();

        $tmpCalendar = ServiceRatePlanCalendar::with([
            'service_rates',
            'service_rates.service_rate_plans'
        ])
            ->get();
        var_export($tmpCalendar);
        die;
//            ->with([
//                'ratesPlansRooms.room.translations' => function ($query) use ($lang, $data) {
//                    $query->where('type', 'room');
//                    $query->whereHas('language', function ($q) use ($lang) {
//                        $q->where('iso', $lang);
//                    });
//                }
//            ])
//            ->with([
//                'ratesPlansRooms.rate_plan.meal.translations' => function ($query) use ($lang) {
//                    $query->where('type', 'meal');
//                    $query->whereHas('language', function ($q) use ($lang) {
//                        $q->where('iso', $lang);
//                    });
//                }
//            ])
//            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
//            ->whereHas('ratesPlansRooms.rate_plan', function ($query) use ($service_id) {
//                $query->where('hotel_id', $service_id);
//            })
//            ->whereHas('ratesPlansRooms', function ($query) use ($ratesIDs, $data, $request) {
//                $query->whereIn('rates_plans_id', $ratesIDs);
//
//                if ($request->input('room')) {
//                    $query->where('room_id', $request->input('room'));
//                }
//
//                if ($request->input('rate')) {
//                    $query->where('rates_plans_id', $request->input('rate'));
//                }
//            })
//            ->get()
//            ->toArray();
//
//        $calendar = [];
//
//        $channels = Channel::all()->keyBy('id')->toArray();
//
//        foreach ($tmpCalendar as $tmpKey => $tmpCal) {
//            $ratesPlansRoom = $tmpCal['rates_plans_rooms'];
//            $room = $ratesPlansRoom['room'];
//
//            if ($request->input('room')) {
//                if ($room['id'] !== (int)$request->input('room')) {
//                    continue;
//                }
//            }
//
//            if ($request->input('rate')) {
//                if ($ratesPlansRoom['rate_plan']['id'] !== (int)$request->input('rate')) {
//                    continue;
//                }
//            }
//
//            if ($request->input('channel')) {
//                if ($channels[$ratesPlansRoom['channel_id']]['id'] !== (int)$request->input('channel')) {
//                    continue;
//                }
//            }
//
//            $tmpItem = [
//                'ratePlan' => [
//                    'id' => $ratesPlansRoom['rate_plan']['id'],
//                    'name' => $ratesPlansRoom['rate_plan']['name'],
//                ],
//                'room' => [
//                    'id' => $room['id'],
//                    'name' => $room['translations'][0]['value'],
//                    'value' => 0
//                ],
//                'meal' => [
//                    'id' => $ratesPlansRoom['rate_plan']['meal']['id'],
//                    'name' => $ratesPlansRoom['rate_plan']['meal']['translations'][0]['value'],
//                ],
//                'channel' => [
//                    'id' => $channels[$ratesPlansRoom['channel_id']]['id'],
//                    'name' => $channels[$ratesPlansRoom['channel_id']]['name'],
//                ],
//                'rates' => []
//            ];
//
//            if ($tmpCal['policies_rates'] === null) {
//                $tmpItem['policy'] = [
//                    'name' => '',
//                    'max_ab_offset' => $tmpCal['max_ab_offset'],
//                    'min_ab_offset' => $tmpCal['min_ab_offset'],
//                    'min_length_stay' => $tmpCal['min_length_stay'],
//                    'max_length_stay' => $tmpCal['max_length_stay'],
//                    'max_occupancy' => $tmpCal['max_occupancy'],
//                ];
//            } else {
//                $tmpItem['policy'] = [
//                    'id' => $tmpCal['policies_rates']['id'],
//                    'name' => $tmpCal['policies_rates']['name'],
//                    'max_ab_offset' => $tmpCal['policies_rates']['max_ab_offset'],
//                    'min_ab_offset' => $tmpCal['policies_rates']['min_ab_offset'],
//                    'min_length_stay' => $tmpCal['policies_rates']['min_length_stay'],
//                    'max_length_stay' => $tmpCal['policies_rates']['max_length_stay'],
//                    'max_occupancy' => $tmpCal['policies_rates']['max_occupancy'],
//                ];
//            }
//
//            $rateValue = 9999999999;
//            $winValue = 0;
//            foreach ($tmpCal['rate'] as $rates) {
//                $markup = ((float)$ratesMarkups / 100) + 1;
//                if ((float)$rates['price_adult'] != 0 && (float)$rates['price_adult'] < $rateValue) {
//                    $rateValue = (float)$rates['price_adult'] * $markup;
//                    $winValue = number_format((float)$rates['price_adult'] * $markup, 2);
//                }
//
//                if ((float)$rates['price_total'] != 0 && (float)$rates['price_total'] < $rateValue) {
//                    $rateValue = (float)$rates['price_total'] * $markup;
//                    $winValue = number_format((float)$rates['price_total'] * $markup, 2);
//                }
//
//                if ((float)$rates['num_adult'] == 0.0 &&
//                    (float)$rates['num_child'] == 0.0 &&
//                    (float)$rates['num_infant'] == 0.0 &&
//                    (float)$rates['price_adult'] == 0.0 &&
//                    (float)$rates['price_child'] == 0.0 &&
//                    (float)$rates['price_infant'] == 0.0 &&
//                    (float)$rates['price_extra'] == 0.0 &&
//                    (float)$rates['price_total'] > 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Room',
//                        'category' => '',
//                        'amount' => '',
//                        'price' => number_format((float)$rates['price_total'] * $markup, 2)
//                    ];
//                }
//                if ((float)$rates['num_adult'] > 0 &&
//                    (float)$rates['num_child'] == 0 &&
//                    (float)$rates['num_infant'] == 0 &&
//                    (float)$rates['price_adult'] > 0 &&
//                    (float)$rates['price_child'] == 0 &&
//                    (float)$rates['price_infant'] == 0 &&
//                    (float)$rates['price_extra'] == 0 &&
//                    (float)$rates['price_total'] == 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Person',
//                        'category' => 'Adult',
//                        'amount' => $rates['num_adult'],
//                        'price' => number_format((float)$rates['price_adult'] * $markup, 2)
//                    ];
//                }
//                if ((float)$rates['num_adult'] == 0 &&
//                    (float)$rates['num_child'] > 0 &&
//                    (float)$rates['num_infant'] == 0 &&
//                    (float)$rates['price_adult'] == 0 &&
//                    (float)$rates['price_child'] > 0 &&
//                    (float)$rates['price_infant'] == 0 &&
//                    (float)$rates['price_extra'] == 0 &&
//                    (float)$rates['price_total'] == 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Person',
//                        'category' => 'Child',
//                        'amount' => $rates['num_child'],
//                        'price' => number_format((float)$rates['price_child'] * $markup, 2)
//                    ];
//                }
//                if ((float)$rates['num_adult'] == 0 &&
//                    (float)$rates['num_child'] == 0 &&
//                    (float)$rates['num_infant'] > 0 &&
//                    (float)$rates['price_adult'] == 0 &&
//                    (float)$rates['price_child'] == 0 &&
//                    (float)$rates['price_infant'] > 0 &&
//                    (float)$rates['price_extra'] == 0 &&
//                    (float)$rates['price_total'] == 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Person',
//                        'category' => 'Infant',
//                        'amount' => $rates['num_infant'],
//                        'price' => number_format((float)$rates['price_infant'] * $markup, 2)
//                    ];
//                }
//                if ((float)$rates['num_adult'] == 0 &&
//                    (float)$rates['num_child'] == 0 &&
//                    (float)$rates['num_infant'] == 0 &&
//                    (float)$rates['price_adult'] > 0 &&
//                    (float)$rates['price_total'] == 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Price',
//                        'category' => 'Adult',
//                        'amount' => '',
//                        'price' => number_format((float)$rates['price_adult'] * $markup, 2)
//                    ];
//                }
//                if ((float)$rates['num_adult'] == 0 &&
//                    (float)$rates['num_child'] == 0 &&
//                    (float)$rates['num_infant'] == 0 &&
//                    (float)$rates['price_child'] > 0 &&
//                    (float)$rates['price_total'] == 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Price',
//                        'category' => 'Child',
//                        'amount' => '',
//                        'price' => number_format((float)$rates['price_child'] * $markup, 2)
//                    ];
//                }
//                if ((float)$rates['num_adult'] == 0 &&
//                    (float)$rates['num_child'] == 0 &&
//                    (float)$rates['num_infant'] == 0 &&
//                    (float)$rates['price_infant'] > 0 &&
//                    (float)$rates['price_total'] == 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Price',
//                        'category' => 'Infant',
//                        'amount' => '',
//                        'price' => number_format((float)$rates['price_infant'] * $markup, 2)
//                    ];
//                }
//                if ((
//                        (float)$rates['num_adult'] > 0 ||
//                        (float)$rates['num_child'] > 0 ||
//                        (float)$rates['num_infant'] > 0
//                    ) &&
//                    (float)$rates['price_adult'] == 0 &&
//                    (float)$rates['price_child'] == 0 &&
//                    (float)$rates['price_infant'] == 0 &&
//                    (float)$rates['price_extra'] == 0 &&
//                    (float)$rates['price_total'] > 0
//                ) {
//                    $tmpItem['rates'][] = [
//                        'id' => $rates['id'],
//                        'type' => 'By Occupancy',
//                        'category' => '',
//                        'amount' => 'A:' . $rates['num_adult'] . ' / C: ' . $rates['price_child'] . ' / I: ' . $rates['price_infant'],
//                        'price' => number_format((float)$rates['price_total'] * $markup, 2)
//                    ];
//                }
//            }
//
//            $tmpItem['room']['value'] = $winValue;
//
//            $tmpItemCollection = collect($tmpItem['rates']);
//            $tmpItemArray = $tmpItemCollection->sortBy('type')->toArray();
//            $tmpItem['rates'] = $tmpItemArray;
//
////            if ((float)$winValue > 0) {
//            if (array_key_exists($tmpCal['date'], $calendar)) {
//                $calendar[$tmpCal['date']][] = $tmpItem;
//            } else {
//                $calendar[$tmpCal['date']] = [$tmpItem];
//            }
//            }
//        }

//        return Response::json(['success' => true, 'data' => $calendar]);
    }

}
