<?php

namespace App\Http\Controllers;

use App\Service;
use Carbon\Carbon;
use App\BusinessRegion;
use App\ClientServiceOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientServiceOffersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $client_id = $request->input('client_id');
        $region_id = $request->input('region_id');

        $region = BusinessRegion::with('countries')->find($region_id);
        $countryIds = $region->countries->pluck('id');

        $offers = ClientServiceOffer::where('client_id', $client_id)
                ->whereHas('service_rate.service.serviceOrigin', function($q) use ($countryIds) {
                    $q->whereIn('country_id', $countryIds);
                })
                ->with([
                    'service_rate.service' => function ($q) use ($countryIds) {
                        $q->select('id', 'aurora_code', 'name')
                        ->whereHas('serviceOrigin', function($query) use ($countryIds) {
                            $query->whereIn('country_id', $countryIds);
                        });
                    }
                ]);

        $count = $offers->count();

        if ($paging === 1) {
            $offers = $offers->take($limit)->orderBy('id', 'desc')->get(
                [
                    'id',
                    'client_id',
                    'service_rate_id',
                    'date_from',
                    'date_to',
                    'value',
                    'is_offer',
                    'status',
                    'created_at',
                ]
            );
        } else {
            $offers = $offers->skip($limit * ($paging - 1))->take($limit)
                ->orderBy('id', 'desc')
                ->get([
                    'id',
                    'client_id',
                    'service_rate_id',
                    'date_from',
                    'date_to',
                    'value',
                    'is_offer',
                    'status',
                    'created_at',
                ]);
        }

        $data = [
            'data' => $offers,
            'count' => $count,
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
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'required',
                'service_rate_id' => 'array',
                'client_id' => 'required',
                'date_from' => 'required',
                'date_to' => 'required',
                'value_offer' => 'required|min:1',
                'is_offer' => 'required',
                'all_services' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
            } else {
                $all_services = $request->input('all_services');
                $date_from = Carbon::parse($request->input('date_from'))->format('Y-m-d');
                $date_to = Carbon::parse($request->input('date_to'))->format('Y-m-d');
                $value_offer = $request->input('value_offer');
                $is_offer = $request->input('is_offer');
                if ($all_services) {

                } else {
                    foreach ($request->input('service_rate_id') as $service_rate_id) {
                        $validate_range = $this->validateRangeRate($request->input('client_id'),
                            $service_rate_id, $date_from, $date_to);
                        if ($validate_range) {
                            $response = ['success' => false, 'message' => 'RATE_RANGE_EXIST'];
                            return Response::json($response);
                        } else {
                            $newOffert = new ClientServiceOffer();
                            $newOffert->client_id = $request->input('client_id');
                            $newOffert->service_rate_id = $service_rate_id;
                            $newOffert->date_from = $date_from;
                            $newOffert->date_to = $date_to;
                            $newOffert->value = $value_offer;
                            $newOffert->is_offer = $is_offer;
                            $newOffert->status = 1;
                            $newOffert->save();
                        }
                    }
                }
                return Response::json(['success' => true]);
            }
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function validateRangeRate($client_id, $service_rate_id, $date_from, $date_to)
    {
        $response = false;
        $date_from = date("Y-m-d", strtotime(str_replace('/', '-', $date_from)));
        $date_to = date("Y-m-d", strtotime(str_replace('/', '-', $date_to)));
        $count = DB::select(
            DB::raw("select * from client_service_offers where service_rate_id = '$service_rate_id' and client_id = '$client_id'
                        and (
                            (
                                ('$date_from' >= date_from and '$date_from' <= date_to) or
		                        ('$date_to' >= date_from and '$date_to' <= date_to)
                            ) or
                            (
                                (date_from >= '$date_from' and date_to >= '$date_from') and
		                        (date_from <= '$date_to' and date_to <= '$date_to')
                            )
                        ) and deleted_at is null limit 1
                        "));
        if (count($count) > 0) {
            $response = true;
        }
        return $response;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientServiceOffer  $clientServiceOffert
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientServiceOffer $clientServiceOffert)
    {
        //
    }

    public function updateStatus($id, Request $request)
    {
        try {
            $service = ClientServiceOffer::find($id, ['id', 'status']);
            if ($request->input("status")) {
                $service->status = false;
            } else {
                $service->status = true;
            }
            $service->save();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateOffer($id, Request $request)
    {
        try {
            $service = ClientServiceOffer::find($id, ['id', 'is_offer']);
            if ($request->input("status")) {
                $service->is_offer = false;
            } else {
                $service->is_offer = true;
            }
            $service->save();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientServiceOffer  $clientServiceOffert
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $offer = ClientServiceOffer::find($id);
            $offer->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
