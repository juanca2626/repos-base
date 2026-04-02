<?php

namespace App\Http\Controllers;

use App\Hotel;
use App\HotelTax;
use App\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HotelTaxesController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:taxes.read')->only('index');
        // $this->middleware('permission:taxes.create')->only('store');
        // $this->middleware('permission:taxes.update')->only('update');
        // $this->middleware('permission:taxes.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input('lang');
        $type = $request->input('type');
        $hotel_id = $request->input('hotel_id');
        $hotel = Hotel::find($hotel_id);
        $taxes = Tax::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hoteltaxes');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
            ,
            'hotelTaxes' => function ($query) use ($hotel_id) {
                $query->where('hotel_id', $hotel_id);
            }
        ])->where(['country_id' => $hotel->country_id])->get();

        return Response::json(['success' => true, 'data' => $taxes]);
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
        $storeTaxes = $request->input("storeTaxes");

        foreach ($storeTaxes as $key => $value) {
            if (empty($value['hotel_taxes'])) {
                $hotelTax = new HotelTax();
                $hotelTax->amount = $value['value'];
                if (empty($value['status'])) {
                    $hotelTax->status = 0;
                } else {
                    $hotelTax->status = 1;
                }
                $hotelTax->tax_id = $value['id'];
                $hotelTax->hotel_id = $id;
                $hotelTax->save();
            } else {
                foreach ($value['hotel_taxes'] as $key => $valueTax) {
                    $updateTaxt = HotelTax::find($valueTax['id']);
                    $updateTaxt->amount = $value['value'];
                    $updateTaxt->status = $value['status'];
                    $updateTaxt->save();
                }
            }
        }
        return Response::json(['success' => true]);
    }
}
