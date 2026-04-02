<?php

namespace App\Http\Controllers;

use App\Language;
use App\State;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StatesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:states.read')->only('index');
        $this->middleware('permission:states.create')->only('store');
        $this->middleware('permission:states.update')->only('update');
        $this->middleware('permission:states.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        //Todo: Refactorizar Bloque de pagination
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang') ?? 'es';

        $language_id = Language::select('id')->where('iso', $lang)->first()->id;

        $state_ids = [];
        $country_ids = [];

        if ($querySearch) {

            $state_ids = Translation::select('object_id')->where('type', 'state')
                ->where('language_id', $language_id)
                ->where('value', 'like', '%' . $querySearch . '%')
                ->pluck('object_id')
                ->toArray();

            $country_ids = Translation::select('object_id')->where('type', 'country')
                ->where('language_id', $language_id)
                ->where('value', 'like', '%' . $querySearch . '%')
                ->pluck('object_id')
                ->toArray();
        }

        $states = State::with([
            'translations' => function ($query) use ($language_id) {

                $query->where('language_id', $language_id);

            }
        ])
            ->with([
                'country.translations' => function ($query) use ($language_id) {

                    $query->where('language_id', $language_id);

                }
            ]);
        if ($querySearch) {
            $states->whereIn('id', $state_ids);
            $states->orWhereIn('country_id', $country_ids);
        }
        $count = $states->count();

        $states = $states->paginate($limit);

        $data = [
            'data' => $states->items(),
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'translations' => 'required|array',
            'translations.*.state_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ], 422);
        }

        try {
            $state = new State();
            $state->iso = $request->input("iso");
            $state->country_id = $request->input("country_id");
            $state->save();
            $this->saveTranslation($request->input("translations"), 'state', $state->id);
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        $lang = $request->input("lang");
        $state = State::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'state');
                $query->where('object_id', $id);
            }
        ])->with([
            'country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $state]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {

        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'country_id' => "exists:countries,id",
            // 'translations.*.state_name' => 'unique:translations,value,'.$id.',object_id,type,state,iso'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'message' => $errors->getMessageBag()]);
        } else {
            $state = State::find($id);
            $state->iso = $request->input("iso");
            if ($request->input('country_id') != null) {
                $state->country_id = $request->input('country_id');
                $state->save();
            }
            $this->saveTranslation($request->input('translations'), 'state', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $state = State::find($id);

        $state->delete();

        $this->deleteTranslation('state', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $language_id = Language::select('id')->where('iso', $lang)->first()->id;

        $states = State::with([
            'translations' => function ($query) use ($language_id) {
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'country.translations' => function ($query) use ($language_id) {
                $query->where('type', 'country');
                $query->where('language_id', $language_id);
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $states]);
    }

    public function getStates($id, $lang)
    {
        $language_id = Language::where('iso', $lang)->first()->id;
        $states = State::where('country_id', $id)
            ->with([
                'translations' => function ($query) use ($language_id) {
                    $query->select(['object_id', 'value']);
                    $query->where('type', 'state');
                    $query->where('language_id', $language_id);
                }
            ])->get(['id', 'iso', 'country_id']);
        return Response::json(['success' => true, 'data' => $states]);
    }
}
