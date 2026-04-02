<?php

namespace App\Http\Controllers;

use App\GenerateRatesInCalendar;
use App\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\GenerateRatesInCalendarRequest;
use App\RatesPlans;
use App\Http\Traits\GenerateRatesCalendar;
use App\Jobs\ProcessRateInFilesCalendaries;
use App\Jobs\RateCalendaries;
use Exception;

class GenerateRatesInCalendarController extends Controller
{
    use GenerateRatesCalendar;

    public function __construct()
    {
        $this->middleware('permission:bags.read')->only('getBag');
        $this->middleware('permission:bags.create')->only('store');
        $this->middleware('permission:bags.update')->only('update');
        $this->middleware('permission:bags.delete')->only('delete');
    }


    public function getListRates(GenerateRatesInCalendarRequest $request)
    {
        try {

            $params = $request->all();
            $params['perido'] = isset($params['perido']) ? $params['perido'] : date('Y');
            $params['room_id'] = isset($params['room_id']) ? $params['room_id'] : NULL;
            $params['status'] = 1;
            $params['user_add'] = auth()->user()->id;

            $generated = GenerateRatesInCalendar::where('hotel_id', $params['hotel_id'])->where('rates_plans_id', $params['rates_plans_id'])->where('status', '1')->get();

            if (count($generated) > 0) {
                throw new Exception('Hay un proceso ejecutándose, no puede ejecutar esta acción');
            }

            $rangos = $this->generateRates($params['hotel_id'], $params['rates_plans_id'], $params['room_id'], $params['perido']);

            if (count($rangos['date_range_hotel_duplicate']) > 0) {
                throw new Exception('Tiene más de un rango de fechas que duplicaran la tarifa');
            }

            if (count($rangos['rateRoomDate']) == 0) {
                throw new Exception('No hay tarifas con rangos de fechas ha procesar');
            }
            
            $hotel = Hotel::with([ 
                'rates_plans' => function ($query) use ($params) {
                    $query->where('id', $params['rates_plans_id']); 
                }  
              ]
            )->find($params['hotel_id']);
 
            return Response::json(['success' => true, 'rangos' => $rangos['rateRoomDate'], 'hotel' => $hotel]);

        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }

    }

    public function process(GenerateRatesInCalendarRequest $request)
    {
        try{

            // ProcessRateInFilesCalendaries::dispatch(5312, $request->input('files'));

            // return Response::json(['success' => true, 'message' => 'El proceso ya esta ejecutándose']);

            $params = $request->all();
            $params['perido'] = isset($params['perido']) ? $params['perido'] : date('Y');
            $params['room_id'] = isset($params['room_id']) ? $params['room_id'] : NULL;
            $params['status'] = 1;
            $params['user_add'] = auth()->user()->id;
            // $params['files'] = isset($params['files']) ? $params['files'] : NULL;

            $generated = GenerateRatesInCalendar::where('hotel_id',$params['hotel_id'] )->where('rates_plans_id',$params['rates_plans_id'])->where('status','1')->get();

            if(count($generated)>0){
                throw new Exception('Hay un proceso ejecutándose, no puede ejecutar esta acción');
            }

            $rangos  = $this->generateRates($params['hotel_id'],$params['rates_plans_id'],$params['room_id'],$params['perido']);

            if(count($rangos['date_range_hotel_duplicate'])>0){
                throw new Exception('Tiene más de un rango de fechas que duplicaran la tarifa');
            }

            if(count($rangos['rateRoomDate']) == 0){
                throw new Exception('No hay tarifas con rangos de fechas ha procesar');
            }

 
            $generate_rates_in_calendar = GenerateRatesInCalendar::where('hotel_id',$params['hotel_id'] )->where('rates_plans_id',$params['rates_plans_id'])->where('status','0')->first();
            if(!$generate_rates_in_calendar){
                $generate_rates_in_calendar = GenerateRatesInCalendar::create($params);
            }else{
                $generate_rates_in_calendar->status = 1;
                $generate_rates_in_calendar->save();
            }

            //$this->processRates($generate_rates_in_calendar->id);
            RateCalendaries::dispatch($generate_rates_in_calendar->id);

            if(isset($params['files']) and count($params['files'])>0){
                ProcessRateInFilesCalendaries::dispatch($generate_rates_in_calendar->id, $request->input('files'));
            }
            
            return Response::json(['success' => true, 'message' => 'El proceso ya esta ejecutándose']);

        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }

    }

    public function status(Request $request)
    {

        $generated = GenerateRatesInCalendar::where('hotel_id', $request->hotel_id)->where('rates_plans_id', $request->rates_plans_id)->where('status', '0')->get();

        return Response::json(['success' => true, 'status' => count($generated) > 0 ? true : false, 'message' => 'El proceso ya esta ejecutándose']);
    }

}
