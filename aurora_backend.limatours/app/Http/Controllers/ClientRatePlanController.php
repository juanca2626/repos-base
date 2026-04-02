<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ClientRatePlan;
use App\HotelTax;
use App\RatesPlansCalendarys;
use App\RatesPlans;
use App\Http\Traits\CalculateMarkup;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ClientRatePlanController extends Controller
{
    use CalculateMarkup;

    /**
     * @param $hotelID
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function calendar($hotelID, Request $request)
    {
        $lang = $request->input('lang');
        $data = explode('-', $request->input('date'));
        $period = (int)$data[1];
        $from = Carbon::createFromDate((int)$data[1], (int)$data[0], 1);
        $to = $from->copy()->endOfMonth();

        $clientRatePlan = RatesPlans::select('id')
            ->where(['status' => 1 , 'hotel_id' => $hotelID ])
            ->whereDoesntHave('clients_rate_plan', function ($query) use ($request,$period) {
                $query->where(['client_id' => $request->input('client') , 'period' => $period ]);
            })
            ->get();


        $ratesIDs = $clientRatePlan->pluck('id');
        $ratesMarkups = $this->getListRateMarkup($request->input('client') , $period , $hotelID ,$ratesIDs);

        $hotel_impuesto = HotelTax::with('tax')->where('hotel_id', $hotelID)->where('status', '1')->get();

//        print_r($ratesIDs);
//        print_r($ratesMarkups);

//        print_r($clientRatePlan);
//        print_r($ratesIDs);
//        print_r($ratesMarkups);


        $tmpCalendar = RatesPlansCalendarys::with([
            'ratesPlansRooms',
            'ratesPlansRooms.rate_plan',
            'ratesPlansRooms.rate_plan.meal',
            'ratesPlansRooms.room',
            'rate',
            'policiesRates'
        ])
            ->with([
                'ratesPlansRooms.room.translations' => function ($query) use ($lang, $data) {
                    $query->where('type', 'room');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->with([
                'ratesPlansRooms.rate_plan.meal.translations' => function ($query) use ($lang) {
                    $query->where('type', 'meal');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereHas('ratesPlansRooms.rate_plan', function ($query) use ($hotelID) {
                $query->where('hotel_id', $hotelID);
            })
            ->whereHas('ratesPlansRooms', function ($query) use ($ratesIDs, $data, $request) {
                $query->whereIn('rates_plans_id', $ratesIDs);

                if ($request->input('room')) {
                    $query->where('room_id', $request->input('room'));
                }

                if ($request->input('rate')) {
                    $query->where('rates_plans_id', $request->input('rate'));
                }
            })
            ->get()
            ->toArray();

        $calendar = [];

        $channels = Channel::all()->keyBy('id')->toArray();



        foreach ($tmpCalendar as $tmpKey => $tmpCal) {
            $ratesPlansRoom = $tmpCal['rates_plans_rooms'];
            $room = $ratesPlansRoom['room'];
            $markup = ((float)$ratesMarkups[$ratesPlansRoom['rates_plans_id']] / 100) + 1;

            if ($request->input('room')) {
                if ($room['id'] !== (int)$request->input('room')) {
                    continue;
                }
            }

            if ($request->input('rate')) {
                if ($ratesPlansRoom['rate_plan']['id'] !== (int)$request->input('rate')) {
                    continue;
                }
            }

            if ($request->input('channel')) {
                if ($channels[$ratesPlansRoom['channel_id']]['id'] !== (int)$request->input('channel')) {
                    continue;
                }
            }

            $tmpItem = [
                'ratePlan' => [
                    'id' => $ratesPlansRoom['rate_plan']['id'],
                    'name' => $ratesPlansRoom['rate_plan']['name'],
                ],
                'room' => [
                    'id' => $room['id'],
                    'name' => $room['translations'][0]['value'],
                    'value' => 0
                ],
                'meal' => [
                    'id' => $ratesPlansRoom['rate_plan']['meal']['id'],
                    'name' => $ratesPlansRoom['rate_plan']['meal']['translations'][0]['value'],
                ],
                'channel' => [
                    'id' => $channels[$ratesPlansRoom['channel_id']]['id'],
                    'name' => $channels[$ratesPlansRoom['channel_id']]['name'],
                ],
                'rates' => []
            ];

            if ($tmpCal['policies_rates'] === null) {
                $tmpItem['policy'] = [
                    'name' => '',
                    'max_ab_offset' => $tmpCal['max_ab_offset'],
                    'min_ab_offset' => $tmpCal['min_ab_offset'],
                    'min_length_stay' => $tmpCal['min_length_stay'],
                    'max_length_stay' => $tmpCal['max_length_stay'],
                    'max_occupancy' => $tmpCal['max_occupancy'],
                ];
            } else {
                $tmpItem['policy'] = [
                    'id' => $tmpCal['policies_rates']['id'],
                    'name' => $tmpCal['policies_rates']['name'],
                    'max_ab_offset' => $tmpCal['policies_rates']['max_ab_offset'],
                    'min_ab_offset' => $tmpCal['policies_rates']['min_ab_offset'],
                    'min_length_stay' => $tmpCal['policies_rates']['min_length_stay'],
                    'max_length_stay' => $tmpCal['policies_rates']['max_length_stay'],
                    'max_occupancy' => $tmpCal['policies_rates']['max_occupancy'],
                ];
            }

            $rateValue = 9999999999;
            $winValue = 0;
            foreach ($tmpCal['rate'] as $rates) {


                $rates['price_adult'] = $this->calculoImpuestoServicios($hotel_impuesto,$ratesPlansRoom['rate_plan'],$rates['price_adult']);
                $rates['price_child'] = $this->calculoImpuestoServicios($hotel_impuesto,$ratesPlansRoom['rate_plan'],$rates['price_child']);
                $rates['price_infant'] = $this->calculoImpuestoServicios($hotel_impuesto,$ratesPlansRoom['rate_plan'],$rates['price_infant']);
                $rates['price_extra'] = $this->calculoImpuestoServicios($hotel_impuesto,$ratesPlansRoom['rate_plan'],$rates['price_extra']);
                $rates['price_total'] = $this->calculoImpuestoServicios($hotel_impuesto,$ratesPlansRoom['rate_plan'],$rates['price_total']);

                if ((float)$rates['price_adult'] != 0 && (float)$rates['price_adult'] < $rateValue) {
                    $rateValue = (float)$rates['price_adult'] * $markup;
                    $winValue = number_format((float)$rates['price_adult'] * $markup, 2);
                }

                if ((float)$rates['price_total'] != 0 && (float)$rates['price_total'] < $rateValue) {
                    $rateValue = (float)$rates['price_total'] * $markup;
                    $winValue = number_format((float)$rates['price_total'] * $markup, 2);
                }

                if ((float)$rates['num_adult'] == 0.0 &&
                    (float)$rates['num_child'] == 0.0 &&
                    (float)$rates['num_infant'] == 0.0 &&
                    (float)$rates['price_adult'] == 0.0 &&
                    (float)$rates['price_child'] == 0.0 &&
                    (float)$rates['price_infant'] == 0.0 &&
                    (float)$rates['price_extra'] == 0.0 &&
                    (float)$rates['price_total'] > 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Room',
                        'category' => '',
                        'amount' => '',
                        'price' => number_format((float)$rates['price_total'] * $markup, 2)
                    ];
                }
                if ((float)$rates['num_adult'] > 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_adult'] > 0 &&
                    (float)$rates['price_child'] == 0 &&
                    (float)$rates['price_infant'] == 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Person',
                        'category' => 'Adult',
                        'amount' => $rates['num_adult'],
                        'price' => number_format((float)$rates['price_adult'] * $markup, 2)
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] > 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_adult'] == 0 &&
                    (float)$rates['price_child'] > 0 &&
                    (float)$rates['price_infant'] == 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Person',
                        'category' => 'Child',
                        'amount' => $rates['num_child'],
                        'price' => number_format((float)$rates['price_child'] * $markup, 2)
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] > 0 &&
                    (float)$rates['price_adult'] == 0 &&
                    (float)$rates['price_child'] == 0 &&
                    (float)$rates['price_infant'] > 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Person',
                        'category' => 'Infant',
                        'amount' => $rates['num_infant'],
                        'price' => number_format((float)$rates['price_infant'] * $markup, 2)
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_adult'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Adult',
                        'amount' => '',
                        'price' => number_format((float)$rates['price_adult'] * $markup, 2)
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_child'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Child',
                        'amount' => '',
                        'price' => number_format((float)$rates['price_child'] * $markup, 2)
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_infant'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Infant',
                        'amount' => '',
                        'price' => number_format((float)$rates['price_infant'] * $markup, 2)
                    ];
                }
                if ((
                        (float)$rates['num_adult'] > 0 ||
                        (float)$rates['num_child'] > 0 ||
                        (float)$rates['num_infant'] > 0
                    ) &&
                    (float)$rates['price_adult'] == 0 &&
                    (float)$rates['price_child'] == 0 &&
                    (float)$rates['price_infant'] == 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] > 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Occupancy',
                        'category' => '',
                        'amount' => 'A:' . $rates['num_adult'] . ' / C: ' . $rates['price_child'] . ' / I: ' . $rates['price_infant'],
                        'price' => number_format((float)$rates['price_total'] * $markup, 2)
                    ];
                }
            }

            $tmpItem['room']['value'] = $winValue;

            $tmpItemCollection = collect($tmpItem['rates']);
            $tmpItemArray = $tmpItemCollection->sortBy('type')->toArray();
            $tmpItem['rates'] = $tmpItemArray;

//            if ((float)$winValue > 0) {
            if (array_key_exists($tmpCal['date'], $calendar)) {
                $calendar[$tmpCal['date']][] = $tmpItem;
            } else {
                $calendar[$tmpCal['date']] = [$tmpItem];
            }
//            }
        }

        return Response::json(['success' => true, 'data' => $calendar]);
    }

    public function calculoImpuestoServicios($impuestosServicios,$rate_plan,$importe){

        $impuestoCalculados = 0;

        foreach ($impuestosServicios as $impuesto){

            if($rate_plan['taxes'] == 1){
                if($impuesto->tax->type == "t") {
                    $impuestoCalculados = $impuestoCalculados + (($impuesto->amount / 100) * $importe);
                }
            }

            if($rate_plan['services'] == 1){
                if($impuesto->tax->type == "s") {
                    $impuestoCalculados = $impuestoCalculados + (($impuesto->amount / 100) * $importe);
                }
            }
        }

        $importeTotal = $importe + $impuestoCalculados;

        return $importeTotal;

    }
}
