<?php

namespace App\Http\Controllers;

use App\Service;
use App\ServiceRatesOts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ServiceRatesOtsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($service_id)
    {
        $service_rates = ServiceRatesOts::where('service_id', $service_id)
            ->get(['id', 'pax_from', 'pax_to', 'price_adult']);

        $data = [
            'data' => $service_rates,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $service_id = $request->post('service_id');
        $rates_ots = $request->post('rates');
        try {
            DB::beginTransaction();
            foreach ($rates_ots as $rate) {
                if (empty($rate['id'])) {
                    $rate_ots = new ServiceRatesOts();
                    $rate_ots->service_id = $service_id;
                    $rate_ots->pax_from = $rate['pax_from'];
                    $rate_ots->pax_to = $rate['pax_to'];
                    $rate_ots->price_adult = $rate['price_adult'];
                    $rate_ots->save();
                } else {
                    $rate_ots = ServiceRatesOts::find($rate['id']);
                    if ($rate_ots) {
                        $rate_ots->service_id = $service_id;
                        $rate_ots->pax_from = $rate['pax_from'];
                        $rate_ots->pax_to = $rate['pax_to'];
                        $rate_ots->price_adult = $rate['price_adult'];
                        $rate_ots->save();
                    }
                }
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getRatesByServiceCode($service_code)
    {
        $service = Service::where('aurora_code', $service_code)->first(['id']);
        $service_rates = collect();
        if ($service) {
            $service_rates = ServiceRatesOts::where('service_id', $service->id)
                ->orderBy('pax_from')
                ->get(['id', 'pax_from', 'pax_to', 'price_adult']);
        }

        $data = [
            'data' => $service_rates,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceRatesOts  $serviceRatesOts
     * @return \Illuminate\Http\Response
     */
    public function destroy($rate_id)
    {
        try {
            DB::beginTransaction();
            $rate_ots = ServiceRatesOts::find($rate_id);
            $rate_ots->delete();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
