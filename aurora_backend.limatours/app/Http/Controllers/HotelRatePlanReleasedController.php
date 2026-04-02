<?php

namespace App\Http\Controllers;

use App\DateRangeHotel;
use App\HotelRatePlanReleased;
use App\HotelRatePlanReleasedRange;
use App\HotelRatePlanReleasedRangeParam;
use App\RatesPlans;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class HotelRatePlanReleasedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $hotel_id = $request->get('hotel_id');
        $year = ($request->has('year') and !empty($request->get('year'))) ? $request->get('year') : date('Y');
        $rates = RatesPlans::where('hotel_id', $hotel_id)->get();
        $hotel_released = HotelRatePlanReleased::whereIn('rate_plan_id', $rates->pluck('id'))
            ->with([
                'released_ranges' => function ($query) use ($year) {
                    $query->select(['id', 'hotel_rate_plan_released_id', 'date_from', 'date_to']);
                    $query->whereYear('date_from', $year);
                    $query->with([
                        'released_params' => function ($query) {
                            $query->select([
                                'id',
                                'hotel_rate_plan_released_range_id',
                                'room_id',
                                'type',
                                'limit',
                                'to',
                                'qty_released',
                                'type_discount',
                                'value'
                            ]);
                            $query->with([
                                'room' => function ($query) {
                                    $query->select(['id']);
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('slug', 'room_name')->where('type',
                                                'room')->where('language_id', 1);
                                        }
                                    ]);
                                }
                            ]);


                        }

                    ]);
                }
            ])
            ->with([
                'rate_plan' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])->orderBy('rate_plan_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'rate_plan_id']);

        return Response::json(['success' => true, 'data' => $hotel_released]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rates_plans_id = $request->get('rates_plans_id');
        $rooms_id = $request->get('rooms_id');
        $type = $request->get('type');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $limit = $request->get('limit');
        $to = $request->get('to');
        $type_discount = $request->get('type_discount');
        $value = $request->get('value');
        $qty_released = $request->get('qty_released');
        try {
            DB::beginTransaction();
            foreach ($rates_plans_id as $rate_plan_id) {
                $rate_plan_released_find = HotelRatePlanReleased::where('rate_plan_id', $rate_plan_id)->first();
                if ($rate_plan_released_find) {
                    $rate_plan_released = HotelRatePlanReleased::find($rate_plan_released_find->id);
                } else {
                    $rate_plan_released = new HotelRatePlanReleased();
                    $rate_plan_released->rate_plan_id = $rate_plan_id;
                    $rate_plan_released->save();
                }
                if ($this->validateReleasedRanges($rate_plan_released->id, $date_from, $date_to)) {
                    return Response::json(['success' => false, 'error' => 'RANGE_EXIST']);
                } else {
                    $rate_plan_released_ranges = new HotelRatePlanReleasedRange();
                    $rate_plan_released_ranges->hotel_rate_plan_released_id = $rate_plan_released->id;
                    $rate_plan_released_ranges->date_from = $date_from;
                    $rate_plan_released_ranges->date_to = $date_to;
                    if ($rate_plan_released_ranges->save()) {
                        foreach ($rooms_id as $room_id) {
                            if ($this->validateReleased($rate_plan_released_ranges->id, $room_id, $to,$type)) {
                                return Response::json(['success' => false, 'error' => 'ROOM_EXIST']);
                            } else {
                                $rate_plan_released_range_params = new HotelRatePlanReleasedRangeParam();
                                $rate_plan_released_range_params->hotel_rate_plan_released_range_id = $rate_plan_released_ranges->id;
                                $rate_plan_released_range_params->room_id = $room_id;
                                $rate_plan_released_range_params->type = $type;
                                $rate_plan_released_range_params->limit = $limit;
                                $rate_plan_released_range_params->to = $to;
                                $rate_plan_released_range_params->qty_released = $qty_released;
                                $rate_plan_released_range_params->type_discount = $type_discount;
                                $rate_plan_released_range_params->value = $value;
                                $rate_plan_released_range_params->save();
                            }
                        }
                    }
                }
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\HotelRatePlanReleased  $hotelReleased
     * @return \Illuminate\Http\Response
     */
    public function show(HotelRatePlanReleased $hotelReleased)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\HotelRatePlanReleased  $hotelReleased
     * @return \Illuminate\Http\Response
     */
    public function edit(HotelRatePlanReleased $hotelReleased)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\HotelRatePlanReleased  $hotelReleased
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HotelRatePlanReleased $hotelReleased)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\HotelRatePlanReleased  $hotelReleased
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $hotel_released = HotelRatePlanReleasedRangeParam::find($id);
            $hotel_released->delete();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => true, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

    public function getRoomsByRatePlan(Request $request)
    {
        $rates_plans_id = $request->get('rates_id');

        $rooms_rates = collect();
        foreach ($rates_plans_id as $rate_plan_id) {
            $date_range_ids_not_show = DateRangeHotel::select('old_id_date_range')->where('rate_plan_id',
                $rate_plan_id)->whereNotNull('old_id_date_range')->pluck('old_id_date_range')->toArray();

            $date_ranges = DateRangeHotel::where('rate_plan_id', $rate_plan_id)->whereNotIn('id',
                $date_range_ids_not_show)->orderBy('group')->get(['room_id']);

            $room_ids = DateRangeHotel::select('room_id')->where('rate_plan_id',
                $rate_plan_id)->distinct()->pluck('room_id');


            $room_names = Translation::select('object_id', 'value')->where('slug', 'room_name')->where('type',
                'room')->whereIn('object_id', $room_ids)->where('language_id', 1)->get()->toArray();

            foreach ($room_names as $room_name) {
                $rooms_rates->add([
                    'room_id' => $room_name['object_id'],
                    'name' => $room_name['value']
                ]);
            }

        }
        $rooms = collect();

        $rooms_rates = $rooms_rates->groupBy('room_id')->sortBy('room_id')->values()->all();

        foreach ($rooms_rates as $rooms_rate) {
            if ($rooms_rate->count() === count($rates_plans_id)) {
                $rooms->add($rooms_rate[0]);
            }
        }

        return Response::json(['success' => true, 'data' => $rooms]);
    }

    public function getRoomsByRangeId(Request $request)
    {
        $hotel_rate_plan_released_id = $request->get('hotel_rate_plan_released_id');

        $rooms_rates = collect();

        $hotel_rate_plan_released_ranges = HotelRatePlanReleasedRange::where('id',
            $hotel_rate_plan_released_id)
            ->with([
                'hotelRatePlanReleased' => function ($query) {
                    $query->select(['id', 'rate_plan_id']);
                    $query->with([
                        'rate_plan' => function ($query) {
                            $query->select(['id', 'name']);
                        }
                    ]);
                }
            ])->first();

        $room_ids = DateRangeHotel::select('room_id')->where('rate_plan_id', $hotel_rate_plan_released_ranges->hotelRatePlanReleased->rate_plan_id)
            ->distinct()
            ->pluck('room_id');

        $room_names = Translation::select('object_id', 'value')
            ->where('slug', 'room_name')
            ->where('type', 'room')
            ->whereIn('object_id', $room_ids)
            ->where('language_id', 1)
            ->get()
            ->toArray();

        foreach ($room_names as $room_name) {
            $rooms_rates->add([
                'room_id' => $room_name['object_id'],
                'name' => $room_name['value']
            ]);
        }

        $rooms = collect();
        $rooms_rates = $rooms_rates->groupBy('room_id')->sortBy('room_id')->values()->all();
        foreach ($rooms_rates as $rooms_rate) {
            $rooms->add($rooms_rate[0]);
        }

        $date = [
            'rate_plan_name' => $hotel_rate_plan_released_ranges->hotelRatePlanReleased->rate_plan->name.': '.$hotel_rate_plan_released_ranges->date_from.' -> '.$hotel_rate_plan_released_ranges->date_to,
            'rooms' => $rooms
        ];
        return Response::json(['success' => true, 'data' => $date]);
    }

    public function removeByRate($id)
    {
        try {
            DB::beginTransaction();
            HotelRatePlanReleased::find($id)->released_ranges()->each(function (
                $hotel_released_range
            ) {
                $hotel_released_range->released_params()->each(function ($params) {
                    $params->delete();
                });
                $hotel_released_range->delete();
            });
            $hotel_released = HotelRatePlanReleased::find($id);
            $hotel_released->delete();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => true, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

    public function removeByRange($id)
    {
        try {
            DB::beginTransaction();
            HotelRatePlanReleasedRange::find($id)->released_params()->each(function ($params) {
                $params->delete();
            });
            $hotel_released = HotelRatePlanReleasedRange::find($id);
            $hotel_released->delete();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => true, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

    public function validateReleasedRanges($hotel_rate_plan_released_id, $from, $to)
    {
        $query = DB::select(
            DB::raw("select * from hotel_rate_plan_released_ranges where
                        (
                            (
                                ('$from' >= hotel_rate_plan_released_ranges.date_from and '$from' <= hotel_rate_plan_released_ranges.date_to) or
		                        ('$to' >= hotel_rate_plan_released_ranges.date_from and '$to' <= hotel_rate_plan_released_ranges.date_to)
                            ) or
                            (
                                (hotel_rate_plan_released_ranges.date_from >= '$from' and hotel_rate_plan_released_ranges.date_to >= '$from') and
		                        (hotel_rate_plan_released_ranges.date_from <= '$to' and hotel_rate_plan_released_ranges.date_to <= '$to')
                            )
                        ) and hotel_rate_plan_released_ranges.hotel_rate_plan_released_id = '$hotel_rate_plan_released_id' and deleted_at is null limit 1
                        "));

        if (count($query) > 0) {
            $response = true;
        } else {
            $response = false;
        }

        return $response;
    }

    public function validateReleasedRangesYear($hotel_rate_plan_released_id, $year)
    {
        $query = DB::select(
            DB::raw("select * from hotel_rate_plan_released_ranges where
                         hotel_rate_plan_released_ranges.hotel_rate_plan_released_id = '$hotel_rate_plan_released_id' and year(date_from) = '$year' and  deleted_at is null limit 1
                        "));
        return $query;
    }

    public function validateReleased($hotel_rate_plan_released_range_id, $room_id, $to, $type)
    {
        $query = DB::select(
            DB::raw("select * from hotel_rate_plan_released_range_params where hotel_rate_plan_released_range_params.to = '$to' and hotel_rate_plan_released_range_params.hotel_rate_plan_released_range_id = '$hotel_rate_plan_released_range_id' and hotel_rate_plan_released_range_params.room_id = '$room_id' and hotel_rate_plan_released_range_params.type = '$type' and deleted_at is null limit 1"));
        if (count($query) > 0) {
            $response = true;
        } else {
            $response = false;
        }

        return $response;

    }

    public function storeRangeReleased(Request $request)
    {
        $hotel_rate_plan_released_range_id = $request->get('hotel_rate_plan_released_range_id');
        $rooms_id = $request->get('rooms_id');
        $type = $request->get('type');
        $limit = $request->get('limit');
        $to = $request->get('to');
        $type_discount = $request->get('type_discount');
        $value = $request->get('value');
        $qty_released = $request->get('qty_released');

        try {
            DB::beginTransaction();
            foreach ($rooms_id as $room_id) {
                if ($this->validateReleased($hotel_rate_plan_released_range_id, $room_id, $to, $type)) {
                    return Response::json(['success' => false, 'error' => 'ROOM_EXIST']);
                } else {
                    $rate_plan_released_range_params = new HotelRatePlanReleasedRangeParam();
                    $rate_plan_released_range_params->hotel_rate_plan_released_range_id = $hotel_rate_plan_released_range_id;
                    $rate_plan_released_range_params->room_id = $room_id;
                    $rate_plan_released_range_params->type = $type;
                    $rate_plan_released_range_params->limit = $limit;
                    $rate_plan_released_range_params->to = $to;
                    $rate_plan_released_range_params->qty_released = $qty_released;
                    $rate_plan_released_range_params->type_discount = $type_discount;
                    $rate_plan_released_range_params->value = $value;
                    $rate_plan_released_range_params->save();
                }
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

    public function duplicatePeriod(Request $request)
    {
        $rates_plans_id = $request->get('rates_plans_id');
        $year_copy = $request->get('year_copy');
        $year_apply = $request->get('year_apply');
        try {
            $hotel_rates_plans_released = HotelRatePlanReleased::whereIn('rate_plan_id', $rates_plans_id)
                ->with([
                    'released_ranges' => function ($query) use ($year_copy) {
                        $query->select(['id', 'hotel_rate_plan_released_id', 'date_from', 'date_to']);
                        $query->whereYear('date_from', $year_copy);
                        $query->with([
                            'released_params' => function ($query) {
                                $query->select([
                                    'id',
                                    'hotel_rate_plan_released_range_id',
                                    'room_id',
                                    'type',
                                    'limit',
                                    'to',
                                    'qty_released',
                                    'type_discount',
                                    'value'
                                ]);
                            }
                        ]);
                    }
                ])
                ->get(['id', 'rate_plan_id'])->toArray();
            DB::beginTransaction();
            foreach ($hotel_rates_plans_released as $hotel_rate_plan) {
                //Si ya existen datos del año, eliminamos
                $delete_old = $this->validateReleasedRangesYear($hotel_rate_plan['id'], $year_apply);
                foreach ($delete_old as $released_range_old) {
                    HotelRatePlanReleasedRange::find($released_range_old->id)->released_params()->each(function ($params
                    ) {
                        $params->delete();
                    });
                    $hotel_released = HotelRatePlanReleasedRange::find($released_range_old->id);
                    $hotel_released->delete();
                }
                foreach ($hotel_rate_plan['released_ranges'] as $released_range) {
                    $date_from_explode = explode('-', $released_range['date_from']);
                    $date_to_explode = explode('-', $released_range['date_to']);
                    $new_date_from = $year_apply.'-'.$date_from_explode[1].'-'.$date_from_explode[2];
                    $new_date_to = $year_apply.'-'.$date_to_explode[1].'-'.$date_to_explode[2];
                    $new_released_ranges = new HotelRatePlanReleasedRange();
                    $new_released_ranges->hotel_rate_plan_released_id = $hotel_rate_plan['id'];
                    $new_released_ranges->date_from = $new_date_from;
                    $new_released_ranges->date_to = $new_date_to;
                    $new_released_ranges->save();
                    foreach ($released_range['released_params'] as $released_params) {
                        $new_released_params = new HotelRatePlanReleasedRangeParam();
                        $new_released_params->hotel_rate_plan_released_range_id = $new_released_ranges->id;
                        $new_released_params->room_id = $released_params['room_id'];
                        $new_released_params->type = $released_params['type'];
                        $new_released_params->limit = $released_params['limit'];
                        $new_released_params->to = $released_params['to'];
                        $new_released_params->qty_released = $released_params['qty_released'];
                        $new_released_params->type_discount = $released_params['type_discount'];
                        $new_released_params->value = $released_params['value'];
                        $new_released_params->save();
                    }
                }
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

    public function storeRangeReleasedParam(Request $request)
    {
        $hotel_rate_plan_released_range_param_id = $request->get('hotel_rate_plan_released_range_param_id');
        $type_discount = $request->get('type_discount');
        $to = $request->get('to');
        $value = $request->get('value');
        $limit = $request->get('limit');
        $qty_released = $request->get('qty_released');

        try {
            DB::beginTransaction();
            $rate_plan_released_range_params = HotelRatePlanReleasedRangeParam::find($hotel_rate_plan_released_range_param_id);
            $rate_plan_released_range_params->qty_released = $qty_released;
            $rate_plan_released_range_params->type_discount = $type_discount;
            $rate_plan_released_range_params->value = $value;
            $rate_plan_released_range_params->to = $to;
            $rate_plan_released_range_params->limit = $limit;
            $rate_plan_released_range_params->save();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }


}
