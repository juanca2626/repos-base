<?php

namespace App\Http\Controllers;

use App\Meal;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MealsController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:meals.read')->only('index');
        $this->middleware('permission:meals.create')->only('store');
        $this->middleware('permission:meals.update')->only('update');
        $this->middleware('permission:meals.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $meals = Meal::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'meal');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $meals]);
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
            'translations.*.meal_name' => 'unique:translations,value,NULL,id,type,meal'
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
            $meal = new Meal();
            $meal->save();
            $this->saveTranslation($request->input("translations"), 'meal', $meal->id);
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
        $meal = Meal::with('translations')
            ->where('id', "=", $id)
            ->get();

        return Response::json(['success' => true, 'data' => $meal]);
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

        // $validator = Validator::make($request->all(), [
        //     'translations.*.meal_name' => 'unique:translations,value,1,object_id,type,meal'
        // ]);

        // if ($validator->fails()) {
        //     $errors = $validator->errors();

        //     foreach ($errors->all() as $error) {
        //         array_push($arrayErrors, $error);
        //     }

        //     $countErrors++;
        // }
        // if ($countErrors > 0) {
        //     return Response::json(['success' => false, 'errors' => $errors]);
        // } else {
            $this->saveTranslation($request->input('translations'), 'meal', $id);
            return Response::json(['success' => true]);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $meal = Meal::find($id);

        $meal->delete();

        $this->deleteTranslation('meal', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $meals = Meal::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'meal');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        $data = [];

        foreach ($meals as $meal) {
            $data[] = [
                'id' => $meal->id,
                'translations' => $meal->translations,
                'text' => $meal->translations[0]['value']
            ];
        }

        return Response::json(['success' => true, 'data' => $data]);
    }
}
