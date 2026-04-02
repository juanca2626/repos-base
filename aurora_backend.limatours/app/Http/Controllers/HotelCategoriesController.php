<?php

namespace App\Http\Controllers;

use App\HotelCategory;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class HotelCategoriesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:hotelcategories.read')->only('index');
        $this->middleware('permission:hotelcategories.create')->only('store');
        $this->middleware('permission:hotelcategories.update')->only('update');
        $this->middleware('permission:hotelcategories.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotelcategories = HotelCategory::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hotelcategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $hotelcategories]);
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
            'translations.*.hotelcategory_name' => 'unique:translations,value,NULL,id,type,hotelcategory'
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
            $hotelcategory = new HotelCategory();
            $hotelcategory->save();
            $this->saveTranslation($request->input("translations"), 'hotelcategory', $hotelcategory->id);
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
        $hotelcategories = HotelCategory::with('translations')->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $hotelcategories]);
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
            'translations.*.hotelcategory_name' => 'unique:translations,value,' . $id . ',object_id,type,hotelcategory'
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
            $this->saveTranslation($request->input('translations'), 'hotelcategory', $id);
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
        $hotelcategories = HotelCategory::find($id);

        $hotelcategories->delete();

        $this->deleteTranslation('hotelcategory', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $hotelcategories = HotelCategory::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hotelcategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $hotelcategories]);
    }
}
