<?php

namespace App\Http\Controllers;

use App\Language;
use App\Requeriment;
use App\Requirement;
use App\RequirementService;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RequirementController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:requirements.read')->only('index');
        $this->middleware('permission:requirements.create')->only('store');
        $this->middleware('permission:requirements.update')->only('update');
        $this->middleware('permission:requirements.delete')->only('delete');
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

        $requirement = Requirement::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'requirement');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $requirement->count();
        if ($querySearch) {
            $requirement->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'requirement');
                    $query->where('value', 'like', '%' . $querySearch . '%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }

        if ($paging === 1) {
            $requirement = $requirement->take($limit)->get();
        } else {
            $requirement = $requirement->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $requirement,
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
            'translations.*.requirement_name' => 'unique:translations,value,NULL,id,type,requirement'
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
            $requirement = new Requirement();
            $requirement->save();
            $this->saveTranslation($request->input("translations"), 'requirement', $requirement->id);
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
        $experience = Requirement::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'requirement');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $experience]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.requirement_name' => 'unique:translations,value,' . $id . ',object_id,type,requirement'
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
            $requirement = Requirement::find($id);
            $requirement->save();
            $this->saveTranslation($request->input("translations"), 'requirement', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $requirement = Requirement::find($id);
        $requirement_used = RequirementService::where('requirement_id', $id)->get();
        if ($requirement_used->count() == 0) {
            $requirement->delete();
            $this->deleteTranslation('requirement', $id);
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
        $requirement = Requirement::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'requirement');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $requirement]);
    }

    public function selectBoxList(Request $request)
    {
        $lang = $request->has('lang') ? $request->input('lang') : 'es';
        $language = Language::where('iso', $lang)->first();

        $requirements = collect();
        $requirement_query = Requirement::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'requirement');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $requirement_query->transform(function($query) use ($requirements){
            $requirements->add([
                'id' => $query->id,
                'name' => ($query->translations->count() > 0) ? $query->translations[0]['value'] : '',
            ]);
        });

        return Response::json(['success' => true, 'data' => $requirements]);
    }
}
