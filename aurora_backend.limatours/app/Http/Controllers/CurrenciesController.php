<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CurrenciesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:currencies.read')->only('index');
        $this->middleware('permission:currencies.create')->only('store');
        $this->middleware('permission:currencies.update')->only('update');
        $this->middleware('permission:currencies.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $currencies = Currency::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'currency');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $currencies]);
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
            'translations.*.currency_name' => 'unique:translations,value,NULL,id,type,currency'
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
            $currency = new Currency();
            $currency->symbol = $request->input('symbol');
            $currency->iso = $request->input('iso');
            $currency->exchange_rate = $request->input('exchange_rate');
            $currency->save();
            $this->saveTranslation($request->input("translations"), 'currency', $currency->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $currency = Currency::with('translations')->where('id', "=", $id)->first();

        return Response::json(['success' => true, 'data' => $currency]);
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
            'translations.*.currency_name' => 'unique:translations,value,' . $id . ',object_id,type,currency'
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
            $currency = Currency::find($id);
            $currency->symbol = $request->input('symbol');
            $currency->iso = $request->input('iso');
            $currency->exchange_rate = $request->input('exchange_rate');
            $currency->save();

            $this->saveTranslation($request->input('translations'), 'currency', $id);
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
        $currency = Currency::find($id);

        $currency->delete();

        $this->deleteTranslation('currency', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $currencies = Currency::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'currency');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $currencies]);
    }
}
