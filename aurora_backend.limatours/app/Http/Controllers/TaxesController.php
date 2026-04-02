<?php

namespace App\Http\Controllers;

use App\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TaxesController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:taxes.read')->only('index');
        $this->middleware('permission:taxes.create')->only('store');
        $this->middleware('permission:taxes.update')->only('update');
        $this->middleware('permission:taxes.delete')->only('delete');
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
        $country_id = $request->input('country_id');
        $taxes = Tax::with([
            'country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('type', $type)->where('country_id', $country_id)->orderBy('country_id')->get();
        return Response::json(['success' => true, 'data' => $taxes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'name' => "required|string|max:100",
            'value' => "required|numeric|min:0.01",
            'type' => "required|in:t,s",
            'country_id' => 'required|exists:countries,id'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $tax = new Tax();
            $tax->name = $request->input('name');
            $tax->value = $request->input('value');
            $tax->type = $request->input('type');
            $tax->country_id = $request->input('country_id');
            $tax->save();

            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        $lang = $request->input('lang');
        $tax = Tax::with([
            'country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $tax]);
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
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'name' => "required|string|max:100",
            'value' => "required|numeric|min:0.01",
            'type' => "required|in:t,s",
            'country_id' => 'required|exists:countries,id'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $tax = Tax::find($id);
            $tax->name = $request->input('name');
            $tax->value = $request->input('value');
            $tax->type = $request->input('type');
            $tax->country_id = $request->input('country_id');
            $tax->save();

            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $tax = Tax::find($id);
        $tax->delete();

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $taxes = Tax::select('id', 'name')->get();
        $result = [];
        foreach ($taxes as $tax) {
            array_push($result, ['text' => $tax->name, 'value' => $tax->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }
}
