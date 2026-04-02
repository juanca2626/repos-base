<?php

namespace App\Http\Controllers;

use App\BusinessRegionsCountry;
use App\ClientPackageOffer;
use App\ClientServiceOffer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientPackageOfferController extends Controller
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
        $business_region_id = $request->input('region_id');

        $regions_country_ids = BusinessRegionsCountry::where('business_region_id', $business_region_id)->pluck('country_id'); 
        
        $offers = ClientPackageOffer::with([
            'rate_plan' => function ($q) {
                $q->with([
                    'package' => function ($q) {
                        $q->select('id', 'code');
                        $q->with([
                            'translations' => function ($query) {
                                $query->select(['name', 'package_id']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
                $q->with([
                    'service_type.translations' => function ($query) {
                        $query->select(['value','object_id']);
                        $query->where('type', 'servicetype');
                        $query->where('language_id', 1);
                    }
                ]);
            }
        ])->whereHas('rate_plan', function ($q) use ($regions_country_ids) {
            $q->whereHas('package', function ($q) use ($regions_country_ids) {
                $q->whereIn('country_id', $regions_country_ids);
            });
        })
        ->where('client_id', $client_id);

        $count = $offers->count();

        if ($paging === 1) {
            $offers = $offers->take($limit)->orderBy('id', 'desc')->get(
                [
                    'id',
                    'client_id',
                    'package_plan_rate_id',
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
                    'package_plan_rate_id',
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
                            $newOffert = new ClientPackageOffer();
                            $newOffert->client_id = $request->input('client_id');
                            $newOffert->package_plan_rate_id = $service_rate_id;
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
            DB::raw("select * from client_package_offers where package_plan_rate_id = '$service_rate_id' and client_id = '$client_id'
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

    public function updateStatus($id, Request $request)
    {
        try {
            $service = ClientPackageOffer::find($id, ['id', 'status']);
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
            $service = ClientPackageOffer::find($id, ['id', 'is_offer']);
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
     * @param  \App\ClientPackageOffer  $clientPackageOffer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $offer = ClientPackageOffer::find($id);
            $offer->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
