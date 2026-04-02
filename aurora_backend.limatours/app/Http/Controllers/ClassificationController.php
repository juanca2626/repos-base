<?php

namespace App\Http\Controllers;

use App\Classification;
use App\Service;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClassificationController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:classifications.read')->only('index');
        $this->middleware('permission:classifications.create')->only('store');
        $this->middleware('permission:classifications.update')->only('update');
        $this->middleware('permission:classifications.delete')->only('delete');
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

        $classification = Classification::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'classification');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'classification');
            }
        ]);

        $count = $classification->count();
        if ($querySearch) {
            $classification->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'classification');
                    $query->where('value', 'like', '%' . $querySearch . '%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }

        if ($paging === 1) {
            $classification = $classification->take($limit)->get();
        } else {
            $classification = $classification->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $classification,
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
            'translations.*.classification_name' => 'unique:translations,value,NULL,id,type,classification'
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
            $classification = new Classification();
            $classification->save();
            $this->saveTranslation($request->input("translations"), 'classification', $classification->id);
            return Response::json(['success' => true,'object_id' => $classification->id]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Classification $classificationService
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $classification = Classification::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'classification');
                $query->where('object_id', $id);
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'classification');
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $classification]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Classification $classificationService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.classification_name' => 'unique:translations,value,' . $id . ',object_id,type,classification'
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
            $classification = Classification::find($id);
            $classification->save();
            $this->saveTranslation($request->input("translations"), 'classification', $id);
            return Response::json(['success' => true, 'object_id' => $classification->id]);
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Classification $classificationService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $classification_used = Service::where('classification_id', $id)->get();
        if ($classification_used->count() == 0) {
            $classification = Classification::find($id);
            $classification->delete();
            $this->deleteTranslation('classification', $id);
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
        $classification = Classification::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'classification');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $classification]);
    }
}
