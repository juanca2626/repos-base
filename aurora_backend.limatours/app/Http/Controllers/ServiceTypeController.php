<?php

namespace App\Http\Controllers;

use App\Language;
use App\Service;
use App\ServiceType;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceTypeController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:servicetypes.read')->only('index');
        $this->middleware('permission:servicetypes.create')->only('store');
        $this->middleware('permission:servicetypes.update')->only('update');
        $this->middleware('permission:servicetypes.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $serviceTypes = ServiceType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $serviceTypes->count();
        if ($querySearch) {
            $serviceTypes->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'servicetype');
                    $query->where('value', 'like', '%'.$querySearch.'%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }

        if ($paging === 1) {
            $serviceTypes = $serviceTypes->take($limit)->get();
        } else {
            $serviceTypes = $serviceTypes->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $serviceTypes,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
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
            'code' => 'unique:service_types,code',
            'abbreviation' => 'unique:service_types,abbreviation',
            'translations.*.servicetype_name' => 'unique:translations,value,NULL,id,type,servicetype'
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
            $serviceType = new ServiceType();
            $serviceType->code = $request->input('code');
            $serviceType->abbreviation = $request->input('abbreviation');
            $serviceType->save();
            $this->saveTranslation($request->input("translations"), 'servicetype', $serviceType->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $serviceType = ServiceType::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'servicetype');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $serviceType]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'code' => 'unique:service_types,code,'.$id.',id',
            'abbreviation' => 'unique:service_types,abbreviation,'.$id.',id',
            'translations.*.servicetype_name' => 'unique:translations,value,'.$id.',object_id,type,servicetype'
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
            $serviceType = ServiceType::find($id);
            $serviceType->code = $request->input('code');;
            $serviceType->abbreviation = $request->input('abbreviation');
            $serviceType->save();
            $this->saveTranslation($request->input("translations"), 'servicetype', $serviceType);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subcategory_used = Service::where('service_type_id', $id)->take(1)->get();
        if ($subcategory_used->count() == 0) {
            $experience = ServiceType::find($id);
            $experience->delete();
            $this->deleteTranslation('servicetype', $id);
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }

        return Response::json(['success' => $success, 'used' => $used]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $restrictions = ServiceType::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'servicetype');
                $query->where('language_id', $language->id);
            }
        ])->get(['id', 'code', 'abbreviation']);
        return Response::json(['success' => true, 'data' => $restrictions]);
    }
}
