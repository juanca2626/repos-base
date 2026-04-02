<?php

namespace App\Http\Controllers;

use App\BusinessRegionsCountry;
use App\Client;
use App\Package;
use App\PackageRateSaleMarkup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ClientPackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 10;
        $querySearch = $request->input('query');
        $client_id = $request->input('client_id');
        $business_region_id = $request->input('region_id');

        $regions_country_ids = BusinessRegionsCountry::where('business_region_id', $business_region_id)->pluck('country_id'); 
        
        $market = Client::find($client_id);
        $packages = PackageRateSaleMarkup::select(['id', 'seller_type', 'seller_id', 'package_plan_rate_id'])
            ->where(function ($query) use ($client_id, $market) {
                $query->orWhere('seller_id', $client_id);
                $query->orWhere('seller_id', $market->market_id);
            })
            ->with([
                'plan_rate' => function ($query) {
                    $query->select(['id', 'package_id']);
                }
            ])->where('status', 1)->get();

        $packages_ids = $packages->pluck('plan_rate.package_id')->unique();
        $plan_rate_ids = $packages->pluck('plan_rate.id')->unique();

        $packages_database = Package::whereIn('country_id', $regions_country_ids)->select([
            'id',
            'code'
        ])->with([
            'plan_rates' => function ($query) use ($plan_rate_ids) {
                $query->select(['id', 'package_id', 'name', 'service_type_id']);
                $query->whereIn('id', $plan_rate_ids);
                $query->with([
                    'service_type.translations' => function ($query) {
                        $query->select(['value','object_id']);
                        $query->where('type', 'servicetype');
                        $query->where('language_id', 1);
                    }
                ]);
            }
        ])->with([
            'rated' => function ($query) use ($client_id) {
                $query->select(['id', 'client_id','package_id', 'rated']);
                $query->where('client_id', $client_id);
            }
        ])->with([
            'translations' => function ($query) {
                $query->select(['name', 'package_id']);
                $query->where('language_id', 1);
            }
        ])->when(($querySearch !== null || $querySearch !== ''),
            function ($query) use ($querySearch) {
                $query->where(function ($query) use ($querySearch) {
                    $new_q = str_replace('P', '', str_replace('E', '', $querySearch));
                    $query->orWhere('id', 'like', '%'.$new_q.'%');
                    $query->orWhere('code', 'like', '%'.$querySearch.'%');
                    $query->orWhereHas('translations', function ($q) use ($querySearch) {
                        $q->where('name', 'like', '%'.$querySearch.'%');
                    });
                });
            })->whereIn('id', $packages_ids);
        $count = $packages_database->count();

        if ($paging === 1) {
            $packages_database = $packages_database->take($limit)->get();
        } else {
            $packages_database = $packages_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $packages_database = $packages_database->transform(function ($q) {
            $name = (isset($q->translations) && count($q->translations) > 0) ? $q->translations[0]->name : '';
            $rated = (isset($q->rated) && count($q->rated) > 0) ? $q->rated[0]['rated'] : 0;
            $package = [
              "package_id" => $q->id,
              "code" => $q->code,
              "name" => $name,
              "rated" => $rated,
              "rates" => $q->plan_rates->transform(function ($q) {
                  $name = (isset($q->service_type->translations) && count($q->service_type->translations) > 0) ? $q->service_type->translations[0]->value : '';
                  $rates = [
                      "id" => $q->id,
                      "name" => $q->name,
                      "service_type" => '['.$q->service_type->code.'] '.$name,
                  ];
                  return $rates;
              }),
            ];
            return $package;
        });

        $data = [
            'data' => $packages_database,
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
