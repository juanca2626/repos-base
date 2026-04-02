<?php

namespace App\Http\Controllers;

use App\Country;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CountriesController extends Controller
{
    use Translations;

    public function __construct()
    {
        // $this->middleware('permission:countries.read')->only('index');
        $this->middleware('permission:countries.create')->only('store');
        $this->middleware('permission:countries.update')->only('update');
        $this->middleware('permission:countries.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $countries = Country::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $countries]);
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
            'local_tax' => 'required|boolean',
            'local_service' => 'required|boolean',
            'foreign_tax' => 'required|boolean',
            'foreign_service' => 'required|boolean',
            'translations.*.country_name' => 'unique:translations,value,NULL,id,type,country'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'error' => $arrayErrors]);
        } else {
            $country = new Country();
            $country->iso = $request->input("iso");
            $country->local_tax = $request->input("local_tax");
            $country->local_service = $request->input("local_service");
            $country->foreign_tax = $request->input("foreign_tax");
            $country->foreign_service = $request->input("foreign_service");
            $country->save();
            $this->saveTranslation($request->input("translations"), 'country', $country->id);
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
        $country = Country::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'country');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $country]);
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
            'local_tax' => 'required|boolean',
            'local_service' => 'required|boolean',
            'foreign_tax' => 'required|boolean',
            'foreign_service' => 'required|boolean',
            'translations.*.country_name' => 'unique:translations,value,' . $id . ',object_id,type,country'
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
            $country = Country::find($id);
            $country->iso = $request->input("iso");
            $country->local_tax = $request->input("local_tax");
            $country->local_service = $request->input("local_service");
            $country->foreign_tax = $request->input("foreign_tax");
            $country->foreign_service = $request->input("foreign_service");
            $country->save();
            $this->saveTranslation($request->input('translations'), 'country', $id);
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
        $country = Country::find($id);

        if ($country->hotels()->count() > 0){
            return Response::json([
                'success' => false,
                'message'=> 'Can not delete a Country related to Hotels'
            ]);
        }

        $country->delete();

        $this->deleteTranslation('country', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $countries = Country::with([
            'translations' => function ($query) use ($lang) {
                $query->select('value', 'object_id');
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get(['id','iso','iso_ifx']);
        return Response::json(['success' => true, 'data' => $countries]);
    }
}
