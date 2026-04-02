<?php

namespace App\Http\Controllers;

use App\Amenity;
use App\Imports\AmenitiesImport;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AmenitiesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:amenities.read')->only('index');
        $this->middleware('permission:amenities.create')->only('store');
        $this->middleware('permission:amenities.update')->only('update');
        $this->middleware('permission:amenities.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $amenities = Amenity::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'amenity');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'amenity');
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $amenities]);
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
            'translations.*.amenity_name' => 'unique:translations,value,NULL,id,type,amenity'
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
            $amenity = new Amenity();
            $amenity->status = $request->input('status');
            $amenity->save();

            $this->saveTranslation($request->input("translations"), 'amenity', $amenity->id);

            return Response::json(['success' => true, 'object_id' => $amenity->id]);
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
        $amenity = Amenity::with([
            'translations' => function ($query) use ($id) {
                $query->with(['language']);
                $query->where('type', 'amenity');
                $query->where('object_id', $id);
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'amenity');
            }
        ])->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $amenity]);
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
            'translations.*.amenity_name' => 'unique:translations,value,' . $id . ',object_id,type,amenity'
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
            $amenity = Amenity::find($id);
            $amenity->status = $request->input('status');
            $amenity->save();

            $this->saveTranslation($request->input('translations'), 'amenity', $id);

            return Response::json(['success' => true, 'object_id' => $amenity->id]);
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
        $amenity = Amenity::with('hotels')->find($id);

        //dd($amenity->hotels);

        if(count($amenity->hotels)>0){
            return Response::json(['success' => false, 'message' => 'El registro que esta eliminando tiene hoteles relacionados']);
        }else{

            $amenity->delete();
            $this->deleteTranslation('amenity', $id);
            return Response::json(['success' => true]);
        }


    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $amenities = Amenity::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'amenity');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $amenities]);
    }

    public function updateStatus($id, Request $request)
    {
        $amenity = Amenity::find($id);
        if ($request->input("status")) {
            $amenity->status = false;
        } else {
            $amenity->status = true;
        }
        $amenity->save();
        return Response::json(['success' => true]);
    }

    public function import(Request $request)
    {
        set_time_limit(0);

        try
        {
            $response = ['type' => 'error', 'content' => 'Error al importar los datos de los pasajeros. Por favor, intente nuevamente.'];
            Excel::import(new AmenitiesImport, $request->file('file'));
            
            $response = [
                'type' => 'success',
                'content' => 'Importación ejecutada correctamente.',
            ];
        }
        catch(\SoapFault $ex)
        {
            $response['message'] = $ex;
        }

        return response()->json($response);
    }
}
