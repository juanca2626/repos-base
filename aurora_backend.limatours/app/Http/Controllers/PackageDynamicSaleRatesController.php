<?php

namespace App\Http\Controllers;

use App\PackageDynamicSaleRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageDynamicSaleRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $category_id)
    {
        $ratesPrivate = [];
        $ratesShared = [];
        $sale_id = $request->get('sale_id');
        $service_type_id = $request->get('service_type_id');

        $rates_dynamics = PackageDynamicSaleRate::where('package_plan_rate_category_id', $category_id)
            ->where('package_rate_sale_markup_id', $sale_id)
            ->where('service_type_id', $service_type_id)
            ->orderBy('pax_from')
            ->get();

        foreach ($rates_dynamics as $item) {
            if ($item->service_type_id == 1) { //compartido
                array_push($ratesShared, $item);
            }
            if ($item->service_type_id == 2) {
                array_push($ratesPrivate, $item);

            }
        }
        $rates = [
            'private' => $ratesPrivate,
            'shared' => $ratesShared,
        ];
        $data = [
            'data' => $rates,
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
                'package_plan_rate_category_id' => 'required',
                'service_type_id' => 'required',
                'rates' => 'required'
            ]);
            $category_id = (int)$request->input('package_plan_rate_category_id');
            $rates_dinamyc = $request->input('rates');
            $service_type_id = $request->input('service_type_id');
            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();
                $insert = [];
                foreach ($rates_dinamyc as $item) {
                    if ($item[0]['id'] == null) {
                        $new = new PackageDynamicSaleRate();
                        $new->service_type_id = $service_type_id;
                        $new->package_plan_rate_category_id = $category_id;
                        $new->pax_from = $item[0]['pax_from'];
                        $new->pax_to = $item[0]['pax_to'];
                        $new->simple = $item[0]['simple'];
                        $new->double = $item[0]['double'];
                        $new->triple = $item[0]['triple'];
                        $new->status = 0;
                        $new->save();
                    } else {
                        $update = PackageDynamicSaleRate::find($item[0]['id']);
                        $update->pax_from = $item[0]['pax_from'];
                        $update->pax_to = $item[0]['pax_to'];
                        $update->simple = $item[0]['simple'];
                        $update->double = $item[0]['double'];
                        $update->triple = $item[0]['triple'];
                        $update->status = 0;
                        $update->save();
                    }
                }
                DB::commit();
                return Response::json(['success' => true]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }

    }

    public function update(Request $request)
    {
        $rates = $request->has('rates') ? $request->input('rates') : [];
        if (count($rates) > 0) {
            foreach ($rates as $rate) {
                $update = PackageDynamicSaleRate::find($rate[0]["id"]);
                $update->pax_from = $rate[0]['pax_from'];
                $update->pax_to = $rate[0]['pax_to'];
                $update->simple = $rate[0]['simple'];
                $update->double = $rate[0]['double'];
                $update->triple = $rate[0]['triple'];
                $update->save();
            }
            return \response()->json("Tarifas actualizadas", 200);
        } else {
            return \response()->json("No se pudo actualizar", 500);
        }

    }

}
