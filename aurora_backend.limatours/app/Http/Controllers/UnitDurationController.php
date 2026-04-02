<?php

namespace App\Http\Controllers;

use App\Language;
use App\Service;
use App\Http\Traits\Translations;
use App\UnitDuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UnitDurationController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:unitdurations.read')->only('index');
        $this->middleware('permission:unitdurations.create')->only('store');
        $this->middleware('permission:unitdurations.update')->only('update');
        $this->middleware('permission:unitdurations.delete')->only('delete');
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

        $unit = UnitDuration::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'unitduration');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $unit->count();
        if ($querySearch) {
            $unit->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'unitduration');
                    $query->where('value', 'like', '%' . $querySearch . '%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }

        if ($paging === 1) {
            $unit = $unit->take($limit)->get();
        } else {
            $unit = $unit->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $unit,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.unitduration_name' => 'unique:translations,value,NULL,id,type,unitduration'
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
            $unit = new UnitDuration();
            $unit->save();
            $this->saveTranslation($request->input("translations"), 'unitduration', $unit->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $unit = UnitDuration::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'unitduration');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $unit]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.unitduration_name' => 'unique:translations,value,' . $id . ',object_id,type,unitduration'
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
            $unit = UnitDuration::find($id);
            $unit->save();
            $this->saveTranslation($request->input("translations"), 'unitduration', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\UnitDuration $unitDuration
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unit_duration_used = Service::where('unit_duration_id', $id)->limit(1)->get();
        if ($unit_duration_used->count() == 0) {
            $unit = UnitDuration::find($id);
            $unit->delete();
            $this->deleteTranslation('unitduration', $id);
            $used = false;
            $success = true;
        }else{
            $used = true;
            $success = false;
        }

        return Response::json(['success' => $success, 'used' => $used]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $units = UnitDuration::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'unitduration');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $units]);
    }

    public function getUnitDurations(Request $request)
    {
        $lang = $request->has('lang') ? $request->input('lang') : 'es';
        $language = Language::where('iso', $lang)->first(['id']);
        $units = collect();
        $units_query = UnitDuration::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'unitduration')->where('language_id', $language->id);
                $query->select(['object_id', 'value']);

            }
        ])->get(['id']);

        $units_query->transform(function($query) use ($units){
            $units->add([
                'id' => $query->id,
                'name' => ($query->translations->count() > 0) ? $query->translations[0]['value'] : '',
            ]);
        });


        return Response::json(['success' => true, 'data' => $units]);
    }
}
