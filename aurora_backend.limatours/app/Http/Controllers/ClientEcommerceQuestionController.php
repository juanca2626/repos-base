<?php

namespace App\Http\Controllers;

use App\ClientEcommerceQuestion;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientEcommerceQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clientecommerce.read')->only('index');
        $this->middleware('permission:clientecommerce.create')->only('store');
        $this->middleware('permission:clientecommerce.update')->only('update');
        $this->middleware('permission:clientecommerce.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $client_id)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $questions = ClientEcommerceQuestion::where('client_id', $client_id)
            ->with([
                'question' => function ($query) use ($language) {
                    $query->with([
                        'translations_question' => function ($query) use ($language) {
                            $query->where('language_id', $language->id);
                        }
                    ]);
                    $query->with([
                        'translations_answer' => function ($query) use ($language) {
                            $query->where('language_id', $language->id);
                        }
                    ]);
                    $query->with([
                        'category' => function ($query) use ($language) {
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->orderBy('id', 'desc')
            ->get();
        return Response::json(['success' => true, 'data' => $questions]);
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
            'client_id' => 'required|integer',
            'questions_ids' => 'required|array',
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
            $questions = $request->input('questions_ids');
            foreach ($questions as $question) {
                $find_count = ClientEcommerceQuestion::where('frequent_questions_id', $question)
                    ->where('client_id', $request->input('client_id'))
                    ->limit(1)->get();
                if (count($find_count) == 0) {
                    $category = new ClientEcommerceQuestion();
                    $category->client_id = $request->input('client_id');
                    $category->frequent_questions_id = $question;
                    $category->save();
                }
            }

            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientEcommerceQuestion  $ClientEcommerceQuestion
     * @return \Illuminate\Http\Response
     */
    public function show(ClientEcommerceQuestion $ClientEcommerceQuestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientEcommerceQuestion  $ClientEcommerceQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientEcommerceQuestion $ClientEcommerceQuestion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientEcommerceQuestion  $ClientEcommerceQuestion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientEcommerceQuestion $ClientEcommerceQuestion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientEcommerceQuestion  $ClientEcommerceQuestion
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $question = ClientEcommerceQuestion::find($id);
        if ($question) {
            $question->delete();
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false]);
        }
    }

    public function getGroupByCategory(Request $request, $client_id)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $questions = ClientEcommerceQuestion::where('client_id', $client_id)
            ->with([
                'question' => function ($query) use ($language) {
                    $query->with([
                        'translations_question' => function ($query) use ($language) {
                            $query->where('language_id', $language->id);
                        }
                    ]);
                    $query->with([
                        'translations_answer' => function ($query) use ($language) {
                            $query->where('language_id', $language->id);
                        }
                    ]);
                    $query->with([
                        'category' => function ($query) use ($language) {
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->orderBy('id', 'desc')
            ->get()->groupBy('question.category.translations.*.value');

        $question_by_category = [];

        foreach ($questions as $key => $question) {
            $question_by_category[] = [
                'category' => $key,
                'questions' => []
            ];
        }

        foreach ($questions as $key => $question) {
            foreach ($question as $key_item => $item) {
                $key_search = array_search($key,array_column($question_by_category, 'category'));
                if ($key_search !== false) {
                    $question_by_category[$key_search]['questions'][] = [
                        'question' => $item['question']['translations_question'][0]['value'],
                        'answer' => $item['question']['translations_answer'][0]['value'],
                    ];
                }
            }
        }


        return Response::json(['success' => true, 'data' => $question_by_category]);
    }
}
