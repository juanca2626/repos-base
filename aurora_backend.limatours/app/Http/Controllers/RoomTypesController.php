<?php

namespace App\Http\Controllers;

use App\RoomType;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RoomTypesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:roomtypes.read')->only('index');
        $this->middleware('permission:roomtypes.create')->only('store');
        $this->middleware('permission:roomtypes.update')->only('update');
        $this->middleware('permission:roomtypes.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $roomTypes = RoomType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'roomtype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $roomTypes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.roomtype_name' => 'unique:translations,value,NULL,id,type,roomtype',
            'occupation' => 'required'
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
            $roomType = new RoomType();
            $roomType->occupation = $request->get('occupation');
            $roomType->save();
            $this->saveTranslation($request->input("translations"), 'roomtype', $roomType->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $roomType = RoomType::with([
            'translations' => function ($query) {
                $query->where('type', 'roomtype');
            }
        ])->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $roomType]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.roomtype_name' => 'unique:translations,value,'.$id.',object_id,type,roomtype',
            'occupation' => 'required'
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

            $roomType = RoomType::find($id);
            $roomType->occupation = $request->get('occupation');
            $roomType->save();

            $this->saveTranslation($request->input('translations'), 'roomtype', $id);

            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the sp/**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $roomType = RoomType::find($id);

        $roomType->delete();

        $this->deleteTranslation('roomtype', $id);

        return Response::json(['success' => true]);
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function selectBox(Request $request)
    {
        $lang = $request->input("lang");
        $roomTypes = RoomType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'roomtype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $roomTypes]);
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function allotments(Request $request)
    {
        return Response::json(['success' => true, 'data' => [
            ['id'=> 1, "description" => __('roomtype.simple'), "occupation" => 1],
            ['id'=> 2, "description" => __('roomtype.doble_1'), "occupation" => 2],
            ['id'=> 3, "description" => __('roomtype.doble_2'), "occupation" => 2],
            ['id'=> 4, "description" => __('roomtype.triple'), "occupation" => 3], 
        ]]);
    }    
}
