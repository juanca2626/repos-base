<?php

namespace App\Http\Controllers;

use App\ServiceOrigin;
use App\ServiceTax;
use App\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServiceTaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lang = $request->input('lang');
        $type = $request->input('type');
        $service_id = $request->input('service_id');
        $service = ServiceOrigin::where('service_id',$service_id)->get()->toArray();

        $taxes = Tax::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetaxes');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
            'serviceTaxes' => function ($query) use ($lang,$service_id) {
                $query->where('service_id', $service_id);
            }
        ])->where(['country_id' => $service[0]['country_id']])->get();

        return Response::json(['success' => true, 'data' => $taxes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param \App\ServiceTax $serviceTax
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceTax $serviceTax)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\ServiceTax $serviceTax
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $storeTaxes = $request->input("storeTaxes");
        foreach ($storeTaxes as $key => $value) {
            if (empty($value['service_taxes'])) {
                $serviceTax = new ServiceTax();
                $serviceTax->amount = $value['value'];
                if (empty($value['status'])) {
                    $serviceTax->status = 0;
                } else {
                    $serviceTax->status = 1;
                }
                $serviceTax->tax_id = $value['id'];
                $serviceTax->service_id = $id;
                $serviceTax->save();
            } else {
                foreach ($value['service_taxes'] as $key => $valueTax) {
                    $updateTaxt = ServiceTax::find($valueTax['id']);
                    $updateTaxt->amount = $value['value'];
                    $updateTaxt->status = $value['status'];
                    $updateTaxt->save();
                }
            }
        }
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ServiceTax $serviceTax
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceTax $serviceTax)
    {
        //
    }
}
