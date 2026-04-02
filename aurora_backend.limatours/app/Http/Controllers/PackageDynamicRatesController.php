<?php

namespace App\Http\Controllers;

use App\PackageDynamicRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageDynamicRatesController extends Controller
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
        $rates_dynamics = PackageDynamicRate::where('package_plan_rate_category_id', $category_id)
            ->orderBy('pax_from')->get();
        foreach ($rates_dynamics as $item) {
            if ($item->service_type_id == 1) {
                $ratesShared []= $item;
            }
            if ($item->service_type_id == 2) {
                $ratesPrivate[] = $item;
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
     * @param \Illuminate\Http\Request $request
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
                foreach ($rates_dinamyc as $item) {
                    if ($item[0]['id']  == "") {
                        $new_package_dynamic_rates = new PackageDynamicRate();
                        $new_package_dynamic_rates->service_type_id = $service_type_id;
                        $new_package_dynamic_rates->package_plan_rate_category_id = $category_id;
                        $new_package_dynamic_rates->pax_from = $item[0]['pax_from'];
                        $new_package_dynamic_rates->pax_to = $item[0]['pax_to'];
                        $new_package_dynamic_rates->simple = $item[0]['simple'];
                        $new_package_dynamic_rates->double = $item[0]['double'];
                        $new_package_dynamic_rates->triple = $item[0]['triple'];
                        $new_package_dynamic_rates->save();
                    } else {
                        $new_package_dynamic_rates = PackageDynamicRate::find($item[0]['id']);
                        $new_package_dynamic_rates->pax_from = $item[0]['pax_from'];
                        $new_package_dynamic_rates->pax_to = $item[0]['pax_to'];
                        $new_package_dynamic_rates->simple = $item[0]['simple'];
                        $new_package_dynamic_rates->double = $item[0]['double'];
                        $new_package_dynamic_rates->triple = $item[0]['triple'];
                        $new_package_dynamic_rates->save();
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

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $rates_dynamic = PackageDynamicRate::find($id);
            $rates_dynamic->delete();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
