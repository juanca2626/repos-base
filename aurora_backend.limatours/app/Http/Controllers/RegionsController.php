<?php

namespace App\Http\Controllers;

use App\Market;
use App\MarketRegion;
use App\Region;
use Illuminate\Http\Request;

class RegionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regions = Region::get(['id','name']);
        return response()->json(['success' => true, 'data' => $regions]);

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

    public function getRegionsByMarket(Request $request)
    {
        $markets = $request->post('markets_id');
        $market_regions = collect();
        if (!empty($markets) and count($markets) > 0) {
            $market_regions = MarketRegion::whereIn('market_id', $markets)->get(['region_id']);
            $market_regions = $market_regions->pluck('region_id');
        }
        if ($market_regions->count() === 0) {
            $regions = Region::get(['id', 'title']);
        } else {
            $regions = Region::whereIn('id', $market_regions)->get(['id', 'title']);
        }

        $regions = $regions->transform(function ($query){
            return [
                'id' => $query['id'],
                'name' => $query['title']
            ];
        });

        return response()->json(['success' => true, 'data' => $regions]);
    }

    public function getMarketsByRegions(Request $request)
    {
        $regions = $request->post('regions_id');
        $market_regions = collect();
        if (!empty($regions) and count($regions) > 0) {
            $market_regions = MarketRegion::whereIn('region_id', $regions)->get(['market_id']);
            $market_regions = $market_regions->pluck('market_id');
        }
        if ($market_regions->count() === 0) {
            $markets = Market::get(['id', 'name']);
        } else {
            $markets = Market::whereIn('id', $market_regions)->get(['id', 'name']);
        }

        return response()->json(['success' => true, 'data' => $markets]);
    }
}
