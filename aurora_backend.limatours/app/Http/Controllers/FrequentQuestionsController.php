<?php

namespace App\Http\Controllers;

use App\FrequentQuestions;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class FrequentQuestionsController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:frequentquestions.read')->only('index');
        $this->middleware('permission:frequentquestions.create')->only('store');
        $this->middleware('permission:frequentquestions.update')->only('update');
        $this->middleware('permission:frequentquestions.delete')->only('destroy');
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
        $questionCategory = FrequentQuestions::with([
            'category' => function ($query) use ($language) {
                $query->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language->id);
                    }
                ]);
            }
        ])->with([
            'translations_question' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->with([
            'translations_answer' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
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
            'category_id' => 'required|integer',
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
            $category = new FrequentQuestions();
            $category->question_category_id = $request->input('category_id');
            $category->save();
            $this->saveTranslation($request->input("translations"), 'frequent_question', $category->id);
            $this->saveTranslation($request->input("translations_answer"), 'frequent_question', $category->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return void
     */
    public function show($id)
    {
        $questionCategory = FrequentQuestions::with('category')
            ->with('translations_question')
            ->with('translations_answer')
            ->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $questionCategory]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FrequentQuestions  $frequentQuestions
     * @return \Illuminate\Http\Response
     */
    public function edit(FrequentQuestions $frequentQuestions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
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
            $category = FrequentQuestions::find($id);
            $category->question_category_id = $request->input('category_id');
            $category->save();
            $this->saveTranslation($request->input("translations"), 'frequent_question', $id);
            $this->saveTranslation($request->input("translations_answer"), 'frequent_question', $id);

            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FrequentQuestions  $frequentQuestions
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = FrequentQuestions::with('client_questions')->find($id);
        if (count($category->client_questions) > 0) {
            return Response::json([
                'success' => false,
                'message' => 'El registro no puede ser eliminado por que esta siendo usado en el modulo de clientes'
            ]);
        } else {
            $category->delete();
            $this->deleteTranslation('frequent_question', $id);
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

        $questions = FrequentQuestions::with([
            'category' => function ($query) use ($language) {
                $query->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('language_id', $language->id);
                    }
                ]);
            }
        ])->with([
            'translations_question' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->with([
            'translations_answer' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            }
        ])->get();
        $questions_transform = [];

        foreach ($questions as $question) {
            $questions_transform [] = [
                'category' => (count($question->category->translations) > 0) ? $question->category->translations[0]->value : ' - ',
                'question' => (count($question->translations_question) > 0) ? $question->translations_question[0]->value : ' - ',
                'id' => $question->id
            ];
        }

        return Response::json(['success' => true, 'data' => $questions_transform]);
    }
}
