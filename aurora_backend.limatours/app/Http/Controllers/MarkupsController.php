<?php

namespace App\Http\Controllers;

use App\ClientRatePlan;
use App\HotelClient;
use App\Markup;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ClientHotels;
use App\Http\Traits\ClientServices;

class MarkupsController extends Controller
{
    use  ClientHotels, ClientServices;

    public function __construct()
    {
        $this->middleware('permission:markups.read')->only('index');
        $this->middleware('permission:markups.create')->only('store');
        $this->middleware('permission:markups.update')->only('update');
        $this->middleware('permission:markups.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $clients = Markup::where('client_id', request()->client_id)->where('business_region_id', request()->region_id)->search(request()->search)->get();

        return Response::json(['success' => true, 'data' => $clients]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel' => 'required',
            'service' => 'required',
            'client_id' => 'required',
            'region_id' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $valid_period = Markup::where([
                'period' => $request->input('period'),
                'client_id' => $request->input('client_id'),
                'business_region_id' => $request->input('region_id')
            ])->get();

            if ((!empty($valid_period)) && ($valid_period->count() > 0)) {
                return Response::json(['success' => false, 'message' => 'existing']);
            }
            $markup = new Markup();
            $markup->period = $request->input('period');
            $markup->hotel = $request->input('hotel');
            $markup->service = $request->input('service');
            $markup->status = 1;
            $markup->client_id = $request->input('client_id');
            $markup->business_region_id = $request->input('region_id');
            $markup->save();
//            if ($markup->save()) {
              //$this->storeAllHotel($markup->client_id, $markup->hotel, $markup->period);
              //$this->storeAllService($markup->client_id, $markup->service, $markup->period);
//           }

            return Response::json(['success' => true]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $markups = Markup::where('id', $id)->first();

        return Response::json(['success' => true, 'data' => $markups, 'date' => $this->buildDates()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'hotel' => 'required',
            'service' => 'required',
            'client_id' => 'required',
            'region_id' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $markup = Markup::find($id);
            $markup->hotel = $request->input('hotel');
            $markup->service = $request->input('service');
            $markup->status = $request->input('status');
            $markup->client_id = $request->input('client_id');
            $markup->save();

            if ($markup->save()) {
                $hotel_client = HotelClient::where([
                    'period' => $request->input('period'),
                    'client_id' => $request->input('client_id'),
                    'business_region_id' => $request->input('business_region_id')
                ])->get();
                foreach ($hotel_client as $key => $value) {
//                    $update_hotel_client = HotelClient::find($value->id);
//                    $update_hotel_client->markup = $markup->hotel;
//                    $update_hotel_client->save();
                }

                $client_rate_plan = ClientRatePlan::where([
                    'period' => $request->input('period'),
                    'client_id' => $request->input('client_id'),
                    'business_region_id' => $request->input('region_id')
                ]);
                foreach ($client_rate_plan as $key => $value) {
                    $client_rate_plan = ClientRatePlan::find($value->id);
                    $client_rate_plan->markup = $markup->hotel;
                    $client_rate_plan->save();
                }
            }

            return Response::json(['success' => true]);
        }
    }

    public function updateStatus($id, Request $request)
    {
        $markup = Markup::find($id);
        if ($request->input("status")) {
            $markup->status = false;
        } else {
            $markup->status = true;
        }

        $markup->save();

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $markups = Markup::where('status', 1)->where('business_region_id',request()->region_id)->get();
        $result = [];
        foreach ($markups as $markup) {
            if (!in_array($markup->period, array_column($result, 'text'))) {
                array_push($result, ['text' => $markup->period, 'value' => $markup->id, 'porcen_hotel' => $markup->hotel]);
            }
        }

        return Response::json(['success' => true, 'data' => $result]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $markup = Markup::find($id);

        $markup->delete();

        return Response::json(['success' => true]);
    }

    public function buildDates()
    {
        $dates = [];
        for ($i=0; $i < 5; $i++) {
            $dates[$i] = [
                'value' => $this->incrementDate($i),
            ];
        }
        return $dates;
    }

    private function incrementDate($value)
    {
        $date = Carbon::now();
        $date->addYears($value);
        $date = $date->format('Y');
        return $date;
    }

    public function searchByClientId($client_id)
    {
        $markup = Markup::with('client')->where('client_id', $client_id)->where('period',Carbon::now()->year)->first();

        return Response::json(['success' => true, 'data' => $markup]);
    }

}
