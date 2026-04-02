<?php

namespace App\Http\Controllers;

use App\Language;
use App\QuestionCategory;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class QuestionCategoryController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:questioncategories.read')->only('index');
        $this->middleware('permission:questioncategories.create')->only('store');
        $this->middleware('permission:questioncategories.update')->only('update');
        $this->middleware('permission:questioncategories.delete')->only('destroy');
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
        $questionCategory = QuestionCategory::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'question_category');
                $query->where('language_id', $language->id);
            }
        ])->with([
            'galleries' => function ($query) {
                $query->where('type', 'question_category');
            }
        ])->orderBy('id', 'desc')->get();
        return Response::json(['success' => true, 'data' => $questionCategory]);
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
            $category = new QuestionCategory();
            $category->save();
            $this->saveTranslation($request->input("translations"), 'question_category', $category->id);
            return Response::json(['success' => true, 'object_id' => $category->id]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\QuestionCategory  $questionCategory
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $amenity = QuestionCategory::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'question_category');
                $query->where('object_id', $id);
            }
        ])->with([
            'galleries' => function ($query) {
                $query->where('type', 'question_category');
            }
        ])->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $amenity]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\QuestionCategory  $questionCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(QuestionCategory $questionCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\QuestionCategory  $questionCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.questioncategory_name' => 'unique:translations,value,'.$id.',object_id,type,question_category'
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
            $category = QuestionCategory::find($id);
            $category->save();

            $this->saveTranslation($request->input('translations'), 'question_category', $id);

            return Response::json(['success' => true, 'object_id' => $category->id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\QuestionCategory  $questionCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = QuestionCategory::with('frequent_questions')->find($id);
        if (count($category->frequent_questions) > 0) {
            return Response::json([
                'success' => false,
                'message' => 'El registro no puede ser eliminado por que esta siendo usado en el modulo de preguntas'
            ]);
        } else {
            $category->delete();
            $this->deleteTranslation('question_category', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $questionCategory = QuestionCategory::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'question_category');
                $query->where('language_id', $language->id);
            }
        ])->get();

        return Response::json(['success' => true, 'data' => $questionCategory]);
    }
}
