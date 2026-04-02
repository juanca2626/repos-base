<?php

namespace App\Http\Controllers;

use App\Service;
use App\Http\Traits\Translations;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:units.read')->only('index');
        $this->middleware('permission:units.create')->only('store');
        $this->middleware('permission:units.update')->only('update');
        $this->middleware('permission:units.delete')->only('delete');
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

        $unit = Unit::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'unit');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $unit->count();
        if ($querySearch) {
            $unit->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'unit');
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
            'translations.*.unit_name' => 'unique:translations,value,NULL,id,type,unit'
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
            $unit = new Unit();
            $unit->save();
            $this->saveTranslation($request->input("translations"), 'unit', $unit->id);
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
        $unit = Unit::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'unit');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $unit]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.unit_name' => 'unique:translations,value,' . $id . ',object_id,type,unit'
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
            $unit = Unit::find($id);
            $unit->save();
            $this->saveTranslation($request->input("translations"), 'unit', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unit_used = Service::where('unit_id', $id)->limit(1)->get();
        if ($unit_used->count() == 0) {
            $unit = Unit::find($id);
            $unit->delete();
            $this->deleteTranslation('unit', $id);
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
        $units = Unit::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'unit');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $units]);
    }
}
