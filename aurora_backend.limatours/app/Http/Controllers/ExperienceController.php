<?php

namespace App\Http\Controllers;

use App\Experience;
use App\ExperienceService;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ExperienceController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:experiences.read')->only('index');
        $this->middleware('permission:experiences.create')->only('store');
        $this->middleware('permission:experiences.update')->only('update');
        $this->middleware('permission:experiences.delete')->only('delete');
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

        $experience = Experience::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'experience');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $experience->count();
        if ($querySearch) {
            $experience->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'experience');
                    $query->where('value', 'like', '%' . $querySearch . '%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }

        if ($paging === 1) {
            $experience = $experience->take($limit)->get();
        } else {
            $experience = $experience->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $experience,
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
            'translations.*.experience_name' => 'unique:translations,value,NULL,id,type,experience'
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
            $experience = new Experience();
            $experience->save();
            $this->saveTranslation($request->input("translations"), 'experience', $experience->id);
            return Response::json(['success' => true]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $experience = Experience::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'experience');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $experience]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.experience_name' => 'unique:translations,value,' . $id . ',object_id,type,experience'
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
            $experience = Experience::find($id);
            $experience->save();
            $this->saveTranslation($request->input("translations"), 'experience', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Experience $experience
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $experience_used = ExperienceService::where('experience_id', $id)->limit(1)->get();
        if ($experience_used->count() == 0) {
            $experience = Experience::find($id);
            $experience->delete();
            $this->deleteTranslation('experience', $id);
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
        $experience = Experience::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'experience');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $experience]);
    }
}
