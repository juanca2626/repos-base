<?php

namespace App\Http\Controllers;

//use App\PoliciesRates;
//use App\Rates;
use App\PackageServiceRate;
use App\ServiceInventory;
use App\ServiceRate;
use App\ServiceRateHistory;
use App\ServiceRatePlan;
use App\Http\Traits\Translations;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

// RatesHistory
// RatesPlans;
//use App\RatesPlansCalendarys;
//use App\RatesPlansRooms;

class ServiceRateCostsController extends Controller
{
    use Translations;

    /**
     * @param $service_id
     * @param Request $request
     * @return JsonResponse
     */
    public function index($service_id, Request $request)
    {
        $rates = ServiceRate::select(
            'id',
            'name',
            'service_id',
            'service_type_rate_id'
        )
            ->with(['service_type_rate'])
            ->where('service_id', $service_id)
            ->get();

        return Response::json(['success' => true, 'data' => $rates]);
    }

    /**
     * @param $service_id
     * @param Request $request
     * @return JsonResponse
     */
    public function store($service_id, Request $request)
    {
        $validatePriceDynamic = $this->validatePriceDynamic($service_id);
        if($validatePriceDynamic){
            return Response::json(['success' => false, 'message' => 'Price Dynamic already exists']);
        } 
        
        $service_rate = new ServiceRate();
        $service_rate->name = $request->input('name');
        $service_rate->allotment = $request->input('allotment');
        $service_rate->rate = $request->input('rate');
        $service_rate->taxes = $request->input('taxes');
        $service_rate->services = $request->input('services');
        $service_rate->advance_sales = $request->input('advance_sales');
        $service_rate->promotions = $request->input('promotions');
        $service_rate->flag_process_markup = $request->input('flag_process_markup');
        $service_rate->status = true;
        $service_rate->service_type_rate_id = $request->input('type');
        $service_rate->service_id = $service_id;        
        $service_rate->price_dynamic = (int)$request->input('price_dynamic');
        $service_rate->save();

        $this->saveTranslation($request->input("translations"), 'servicerate', $service_rate->id);

        ServiceRateHistory::create([
            'service_rate_id' => $service_rate->id,
            'service_id' => $service_id,
            'data' => $request->getContent(),
            'dataRooms' => ''
        ]);

        return Response::json(['success' => true, 'rate_plan' => $service_rate->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param $hotelID
     * @param $rateID
     * @param Request $request
     * @return JsonResponse
     */
    public function show($service_id, $rateID, Request $request)
    {
        $ratesPlans = ServiceRate::with('translations')
            ->where('id', $rateID)->first();

        return Response::json(['success' => true, 'data' => $ratesPlans]);
    }

    public function update($service_id, $rateID, Request $request)
    {
        $ratesPlan = ServiceRate::find($rateID);
        $ratesPlan->name = $request->input('name');
        $ratesPlan->allotment = $request->input('allotment');
        $ratesPlan->rate = $request->input('rate');
        $ratesPlan->taxes = $request->input('taxes');
        $ratesPlan->services = $request->input('services');
        $ratesPlan->advance_sales = $request->input('advance_sales');
        $ratesPlan->promotions = $request->input('promotions');
        $ratesPlan->flag_process_markup = $request->input('flag_process_markup');
        $ratesPlan->status = true;
        $ratesPlan->service_type_rate_id = $request->input('type');
        $ratesPlan->price_dynamic = (int)$request->input('price_dynamic');
        $ratesPlan->save();

        $this->saveTranslation($request->input("translations"), 'servicerate', $ratesPlan->id);

        $ratesHistory = ServiceRateHistory::select('id', 'dataRooms', 'created_at')
            ->where('service_rate_id', $ratesPlan->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $rooms = [];


        return Response::json([
            'success' => true,
            'rate_plan' => $ratesPlan->id,
            'history' => $rooms
        ]);
    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param Request $request
//     * @param $hotelID
//     * @param $rateID
//     * @return JsonResponse
//     */
//    public function updateRooms(Request $request, $hotelID, $rateID)
//    {
//
//        if ($request->input('del')) {
//            $ratesPlansRoomsIDs = RatesPlansRooms::where('rates_plans_id', $rateID)
//                ->get()
//                ->pluck('id');
//
//            $ratesPlansCalendarys = RatesPlansCalendarys::whereIn('rates_plans_room_id', $ratesPlansRoomsIDs)
//                ->get();
//
//            $ratesPlansCalendarysID = $ratesPlansCalendarys->pluck('id');
//
//            Rates::whereIn('rates_plans_calendarys_id', $ratesPlansCalendarysID)->delete();
//
//            RatesPlansCalendarys::whereIn('rates_plans_room_id', $ratesPlansRoomsIDs)->delete();
//        }
//
//        $this->storeRooms($request, $hotelID, $rateID, true);
//
//        return Response::json([
//            'success' => true
//        ]);
//
//    }

    public function destroy($service_id, $rateID, Request $request)
    {
//        $ratesPlansRooms = RatesPlansRooms::select('id')
//            ->where('rates_plans_id', $rateID)
//            ->get()
//            ->pluck('id')
//            ->all();
//
//        $ratesPlansCalendarys = RatesPlansCalendarys::select('id')
//            ->whereIn('rates_plans_room_id', $ratesPlansRooms)
//            ->get()
//            ->pluck('id')
//            ->all();
//
//        DB::table('rates')->whereIn('rates_plans_calendarys_id', $ratesPlansCalendarys)->delete();
//
//        DB::table('rates_plans_calendarys')->whereIn('id', $ratesPlansCalendarys)->delete();
//
//        DB::table('rates_plans_rooms')->whereIn('id', $ratesPlansRooms)->delete();
        // Verifico que no sea utilizado en una cotizacion - servicio
        $rate_used = PackageServiceRate::where('service_rate_id', $rateID)->take(1)->get();
        if ($rate_used->count() == 0) {
            ServiceRate::find($rateID)->delete();
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }

//    /**
//     * @param $hotelID
//     * @param Request $request
//     * @return JsonResponse
//     */
//    public function calendar($hotelID, Request $request)
//    {
//        $lang = $request->input('lang');
//        $data = explode('|', $request->input('date'));
//
//        $from = Carbon::createFromDate((int)$data[1], (int)$data[0], 1);
//        $to = $from->copy()->endOfMonth();
//
//        $ratesPlans = RatesPlans::select('id')
//            ->where('hotel_id', $hotelID)->get();
//
//        $calendar = RatesPlansCalendarys::with([
//            'ratesPlansRooms',
//            'ratesPlansRooms.rate_plan',
//            'ratesPlansRooms.room',
//            'rate',
//            'policiesRates',
//            'ratesPlansRooms.rate_plan.meal'
//        ])
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
//            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
//
//        $calendar->whereHas('ratesPlansRooms', function ($query) use ($ratesPlans, $data) {
//            $query->whereIn('rates_plans_id', $ratesPlans);
//
//            if ($data[2]) {
//                $query->where('room_id', $data[2]);
//            }
//
//            if ($data[3]) {
//                $query->where('rates_plans_id', $data[3]);
//            }
//        });
//
//        $calendar = $calendar->get();
//
//        $calendar = $calendar->groupBy('date');
//
//        return Response::json(['success' => true, 'data' => $calendar]);
//    }
//
//    public function calendarUpdate(Request $request)
//    {
//        $rates = Rates::find($request->input('rate')['id']);
//        $rates->price_adult = $request->input('rate')['price_adult'];
//        $rates->price_child = $request->input('rate')['price_child'];
//        $rates->price_infant = $request->input('rate')['price_infant'];
//        $rates->price_extra = $request->input('rate')['price_extra'];
//        $rates->save();
//
//        return Response::json(['success' => true]);
//    }
//
//    public function calendarDelete($hotelID, $calendarID, Request $request)
//    {
//        Rates::where('rates_plans_calendarys_id', $calendarID)->delete();
//        RatesPlansCalendarys::find($calendarID)->delete();
//
//        return Response::json(['success' => true]);
//    }
//
//    public function calendarDeleteAll($hotelID, Request $request)
//    {
//        $data = explode('|', $request->input('date'));
//
//        $from = Carbon::createFromDate((int)$data[1], (int)$data[0], 1);
//        $to = $from->copy()->endOfMonth();
//
//        $ratesPlans = RatesPlans::select('id')->where('hotel_id', $hotelID)->get();
//
//        $calendar = RatesPlansCalendarys::select('id')
//            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
//
//        $calendar->whereHas('ratesPlansRooms', function ($query) use ($ratesPlans, $data) {
//            $query->whereIn('rates_plans_id', $ratesPlans);
//
//            if ($data[2]) {
//                $query->where('room_id', $data[2]);
//            }
//
//            if ($data[3]) {
//                $query->where('rates_plans_id', $data[3]);
//            }
//        });
//
//        $calendar = $calendar->get()->pluck('id');
//
//        Rates::whereIn('rates_plans_calendarys_id', $calendar)->delete();
//        RatesPlansCalendarys::whereIn('id', $calendar)->delete();
//
//        return Response::json(['success' => true]);
//    }
//
    public function selectBox($service_id, Request $request)
    {
        $rates = ServiceRate::select('id', 'name')->where('service_id', $service_id)
            ->where('status', 1)->get();

        return Response::json(['success' => true, 'data' => $rates]);
    }

    public function duplicate(Request $request)
    {
        $service_rates = $request->post('rate_plans');
        $year_from = $request->post('year_from');
        $year_to = $request->post('year_to');

        $alerts = '';

        foreach ($service_rates as $service_rate) {

            // Eliminar los del year_to actuales
            $_service_rate_plans = ServiceRatePlan::where('service_rate_id', $service_rate['id'])
                ->where('date_from', '>=', $year_to . '-01-01')
                ->where('date_from', '<=', $year_to . '-12-31')
                ->count();

            if( $_service_rate_plans > 0 ){
                if( $alerts != '' ){
                    $alerts .= " | ";
                }
                $alerts .= "El plan tarifario n° ".$service_rate['id'] . " ya tiene tarifas para el año ".$year_to;
            } else {

                // Consultar el year_from para insertar nuevos con year_to
                $service_rate_plans = ServiceRatePlan::where('service_rate_id', $service_rate['id'])
                    ->where('date_from', '>=', $year_from . '-01-01')
                    ->where('date_from', '<=', $year_from . '-12-31')
                    ->get();

                if( count( $service_rate_plans ) == 0 ){
                    if( $alerts != '' ){
                        $alerts .= " | ";
                    }
                    $alerts .= "El plan tarifario n° ".$service_rate['id'] .
                        " no tiene tarifas para el año ".$year_from;
                }

                foreach ($service_rate_plans as $rate_plan) {

                    $new_date_from = str_replace($year_from, $year_to, $rate_plan->date_from);
                    $new_date_to = str_replace($year_from, $year_to, $rate_plan->date_to);

                    $_new_service_rate_plan = new ServiceRatePlan();
                    $_new_service_rate_plan->service_rate_id = $rate_plan->service_rate_id;
                    $_new_service_rate_plan->service_cancellation_policy_id = $rate_plan->service_cancellation_policy_id;
                    $_new_service_rate_plan->user_id = $rate_plan->user_id;
                    $_new_service_rate_plan->date_from = $new_date_from;
                    $_new_service_rate_plan->date_to = $new_date_to;
                    $_new_service_rate_plan->monday = $rate_plan->monday;
                    $_new_service_rate_plan->tuesday = $rate_plan->tuesday;
                    $_new_service_rate_plan->wednesday = $rate_plan->wednesday;
                    $_new_service_rate_plan->thursday = $rate_plan->thursday;
                    $_new_service_rate_plan->friday = $rate_plan->friday;
                    $_new_service_rate_plan->saturday = $rate_plan->saturday;
                    $_new_service_rate_plan->sunday = $rate_plan->sunday;
                    $_new_service_rate_plan->pax_from = $rate_plan->pax_from;
                    $_new_service_rate_plan->pax_to = $rate_plan->pax_to;
                    $_new_service_rate_plan->price_adult = $rate_plan->price_adult;
                    $_new_service_rate_plan->price_child = $rate_plan->price_child;
                    $_new_service_rate_plan->price_infant = $rate_plan->price_infant;
                    $_new_service_rate_plan->price_guide = $rate_plan->price_guide;
                    $_new_service_rate_plan->status = $rate_plan->status;
                    $_new_service_rate_plan->save();
                }
            }
        }

        return Response::json(['success' => true, 'alerts' => $alerts]);
    }

    public function validatePriceDynamic($service_id)
    {
        $service_rate = ServiceRate::where('service_id', $service_id)->where('price_dynamic', 1)->first();
        if( $service_rate ){
            return true;
        }else{
            return false;
        }
    }
}
