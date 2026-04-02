<?php

namespace App\Http\Controllers;

use App\ChainsMultipleProperty;
use App\ChainsMultiplePropertyHotels;
use App\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ChainsMultiplePropertyController extends Controller
{
    public function index(Request $request)
    {
        $chain_id = $request->input('id');
        $multiple_properties = ChainsMultipleProperty::where('chain_id', $chain_id)
            ->with([
                'multiple_property_hotel' => function ($query) {
                    $query->select(['id', 'chains_multiple_property_id', 'rate_plan_id']);
                    $query->with([
                        'rates_plans' => function ($query) {
                            $query->select(['id', 'name', 'hotel_id']);
                            $query->with([
                                'hotel' => function ($query) {
                                    $query->select(['id', 'name']);
                                }
                            ]);
                        }
                    ]);
                }
            ])->get();
        $collection_transform = collect();
        foreach ($multiple_properties as $index => $multiple_property) {
            $collection_transform->add([
                'id' => $multiple_property['id'],
                'chain_id' => $multiple_property['chain_id'],
                'discount' => $multiple_property['discount'],
                'property_hotels' => collect(),
            ]);
            $group = $multiple_property['multiple_property_hotel']->groupBy(function ($item, $key) {
                return $item['rates_plans']['hotel']['name'];
            });
//            dd($group->toArray());

            foreach ($group as $index_group => $item_group) {
                $rates = collect();
                foreach ($item_group as $index_group_item => $item) {
                    $rates->add([
                        'id' => $item['id'],
                        'rate' => $item['rates_plans']
                    ]);
                }
                $collection_transform[$index]['property_hotels']->add([
                    'hotel' => $index_group,
                    'rates' => $rates
                ]);

            }

        }

        return Response::json(['success' => true, 'data' => $collection_transform]);
    }

    public function store(Request $request)
    {
        $rates = $request->input('rates');
        $discount = $request->input('discount');
        $chain_id = $request->input('chain_id');

        if (count($rates) > 0) {
            $multi_property = new ChainsMultipleProperty();
            $multi_property->discount = $discount;
            $multi_property->chain_id = $chain_id;
            $multi_property->save();
            foreach ($rates as $rate) {
                $multi_property_rate = new ChainsMultiplePropertyHotels();
                $multi_property_rate->chains_multiple_property_id = $multi_property->id;
                $multi_property_rate->rate_plan_id = $rate;
                $multi_property_rate->save();
            }
        } else {
            return Response::json(['success' => false]);
        }


        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $chain_id = $request->input('chain_id');
        $hotels = Hotel::where('chain_id', $chain_id)->get();
        $result = [];
        foreach ($hotels as $key => $hotel) {
            array_push($result, ['value' => ($key + 1)]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function destroy($id)
    {
        $client_rate_plan = ChainsMultipleProperty::where('id', $id)->delete();
        return Response::json(['success' => true]);
    }

    public function getHotels($chain_id)
    {

        $hotels = Hotel::where('chain_id', $chain_id)
            ->with([
                'rates_plans' => function ($query) {
                    $query->select('id', 'name', 'hotel_id');
                    $query->where('status', 1);
                }
            ])
            ->where('status', 1)
            ->get(['id', 'name', 'chain_id']);
        return Response::json(['success' => true, 'data' => $hotels]);
    }

    public function addPropertyRate(Request $request)
    {
        $rates = $request->input('rates');
        $multi_property_id = $request->input('multi_property_id');
        if (count($rates) > 0) {
            $validate = ChainsMultiplePropertyHotels::where('chains_multiple_property_id',
                $multi_property_id)->whereIn('rate_plan_id', $rates)->count();
            if ($validate === 0) {
                foreach ($rates as $rate) {
                    $multi_property_rate = new ChainsMultiplePropertyHotels();
                    $multi_property_rate->chains_multiple_property_id = $multi_property_id;
                    $multi_property_rate->rate_plan_id = $rate;
                    $multi_property_rate->save();
                }
            } else {
                return Response::json(['success' => false, 'error' => 'Una de las tarifas ya existe']);
            }
        } else {
            return Response::json(['success' => false, 'error' => '']);
        }

        return Response::json(['success' => true, 'error' => '']);
    }

    public function destroyRate($property_rate_id)
    {
        $client_rate_plan = ChainsMultiplePropertyHotels::find($property_rate_id)->delete();
        return Response::json(['success' => true]);
    }

    public function destroyRates(Request $request)
    {
        $rates = $request->input('rates');
        foreach ($rates as $rate) {
            $multi_property_rate = ChainsMultiplePropertyHotels::find($rate);
            $multi_property_rate->delete();
        }
        return Response::json(['success' => true]);
    }
}
