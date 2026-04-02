<?php

namespace App\Http\Controllers;

use App\District;
use App\Language;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class DistrictsController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:districts.read')->only('index');
        $this->middleware('permission:districts.create')->only('store');
        $this->middleware('permission:districts.update')->only('update');
        $this->middleware('permission:districts.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $language_id = Language::select('id')->where('iso',$lang)->first()->id;

        $district_ids = [];
        $city_ids = [];

        if ($querySearch)
        {

            $district_ids = Translation::select('object_id')->where('type','district')
                ->where('language_id',$language_id)
                ->where('value','like','%'.$querySearch.'%')
                ->pluck('object_id')
                ->toArray();

            $city_ids = Translation::select('object_id')->where('type','city')
                ->where('language_id',$language_id)
                ->where('value','like','%'.$querySearch.'%')
                ->pluck('object_id')
                ->toArray();
        }

        $districts = District::with(['translations'=>function($query) use($language_id){

            $query->where('language_id',$language_id);

        }])
            ->with(['city.translations'=>function($query) use($language_id){

                $query->where('language_id',$language_id);

            }])
            ->with([
                'city.state.translations' => function ($query) use ($language_id) {
                    $query->where('language_id',$language_id);
                }
            ])
            ->with([
                'city.state.country.translations' => function ($query) use ($language_id) {
                    $query->where('language_id',$language_id);
                }
            ]);
        if($querySearch){
            $districts->whereIn('id', $district_ids);
            $districts->orWhereIn('city_id', $city_ids);
        }
        $count = $districts->count();

        $districts = $districts->paginate($limit);

        $data = [
            'data' => $districts->items(),
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
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
            'city_id' => "exists:cities,id",
            'translations.*.district_name' => 'unique:translations,value,NULL,id,type,district'
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
            $district = new District();
            $district->city_id = $request->input('city_id');
            $district->iso = $request->input('iso');
            $district->save();

            $this->saveTranslation($request->input("translations"), 'district', $district->id);

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

        $district = District::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'district');
                $query->where('object_id', $id);
            }
        ])->with([
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $district]);
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
            'city_id' => "exists:cities,id",
            'translations.*.district_name' => 'unique:translations,value,' . $id . ',object_id,type,district'
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
            $district = District::find($id);
            if ($request->input('city_id') != null) {
                $district->city_id = $request->input('city_id');
                $district->iso = $request->input('iso');
                $district->save();
            }
            $this->saveTranslation($request->input('translations'), 'district', $id);

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
        $district = District::find($id);

        if ($district->hotels()->count() > 0){
            return Response::json([
                'success' => false,
                'message'=> 'Can not delete a District related to Hotels'
            ]);
        }

        $district->delete();

        $this->deleteTranslation('district', $id);

        return Response::json(['success' => true]);
    }

    public function getDistricts($id, $lang)
    {
        $district = District::where('city_id', $id)
            ->with([
                'translations' => function ($query) use ($lang) {
                    $query->where('type', 'district');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();
        return Response::json(['success' => true, 'data' => $district]);
    }
}
