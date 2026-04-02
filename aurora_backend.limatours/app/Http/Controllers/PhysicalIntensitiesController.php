<?php

namespace App\Http\Controllers;

use App\Language;
use App\Package;
use App\PhysicalIntensity;
use App\Service;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PhysicalIntensitiesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:physicalintensities.read')->only('index');
        $this->middleware('permission:physicalintensities.create')->only('store');
        $this->middleware('permission:physicalintensities.update')->only('update');
        $this->middleware('permission:physicalintensities.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $physicalIntensity = PhysicalIntensity::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'physicalintensity');
                $query->where('language_id', $language->id);

            }
        ])->orderBy('id', 'desc')->get();
        return Response::json(['success' => true, 'data' => $physicalIntensity]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.physicalintensity_name' => 'unique:translations,value,NULL,id,type,physicalintensity',
            'color' => 'required'
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
            $important_info = new PhysicalIntensity();
            $important_info->color = $request->input('color');
            $important_info->order = 1;
            $important_info->save();
            $this->saveTranslation($request->input("translations"), 'physicalintensity',
                $important_info->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PhysicalIntensity  $physicalIntensity
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $physicalIntensity = PhysicalIntensity::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'physicalintensity');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $physicalIntensity]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PhysicalIntensity  $physicalIntensity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.physicalintensity_name' => 'unique:translations,value,'.$id.',object_id,type,physicalintensity'
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
            $physicalIntensity = PhysicalIntensity::find($id);
            $physicalIntensity->color = $request->input('color');
            $physicalIntensity->save();
            $this->saveTranslation($request->input("translations"), 'physicalintensity', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PhysicalIntensity  $physicalIntensity
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $physical_intensity_service_used = Service::where('physical_intensity_id', $id)->limit(1)->get()->toArray();
        $physical_intensity_package_used = Package::where('physical_intensity_id', $id)->limit(1)->get()->toArray();

        if (count($physical_intensity_service_used) == 0 and count($physical_intensity_package_used) == 0) {
            $physicalIntensity = PhysicalIntensity::find($id);
            $physicalIntensity->delete();
            $this->deleteTranslation('physicalintensity', $id);
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $physical_intensities = PhysicalIntensity::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'physicalintensity');
                $query->where('language_id', $language->id);
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $physical_intensities]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function selectBoxList(Request $request)
    {
        $lang = $request->has('lang') ? $request->input('lang') : 'es';
        $language = Language::where('iso', $lang)->first();
        $physical_intensities = collect();

        $physical_intensities_query = PhysicalIntensity::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'physicalintensity');
                $query->where('language_id', $language->id);
            }
        ])->orderBy('order')->get(['id','color']);

        $physical_intensities_query->transform(function($query) use ($physical_intensities){
            $physical_intensities->add([
                'id' => $query->id,
                'color' => $query->color,
                'name' => ($query->translations->count() > 0) ? $query->translations[0]['value'] : '',
            ]);
        });


        return Response::json(['success' => true, 'data' => $physical_intensities]);
    }
}
