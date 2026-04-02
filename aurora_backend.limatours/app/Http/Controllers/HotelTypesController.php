<?php

namespace App\Http\Controllers;

use App\HotelType;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class HotelTypesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:hoteltypes.read')->only('index');
        $this->middleware('permission:hoteltypes.create')->only('store');
        $this->middleware('permission:hoteltypes.update')->only('update');
        $this->middleware('permission:hoteltypes.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotelTypes = HotelType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hoteltype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $hotelTypes]);
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
            'translations.*.hoteltype_name' => 'unique:translations,value,NULL,id,type,hoteltype'
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
            $hoteltype = new HotelType();
            $hoteltype->save();
            $this->saveTranslation($request->input("translations"), 'hoteltype', $hoteltype->id);
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
        $hotelType = HotelType::with([
            'translations' => function ($query) {
                $query->where('type', 'hoteltype');
            }
        ])->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $hotelType]);
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
            'translations.*.hoteltype_name' => 'unique:translations,value,' . $id . ',object_id,type,hoteltype'
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
            $this->saveTranslation($request->input('translations'), 'hoteltype', $id);
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
        $hotelType = HotelType::find($id);

        $hotelType->delete();

        $this->deleteTranslation('hoteltype', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $hotelTypes = HotelType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hoteltype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $hotelTypes]);
    }
}
