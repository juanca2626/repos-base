<?php

namespace App\Http\Controllers;

use App\ClientRestriction;
use App\Language;
use App\Restriction;
use App\RestrictionService;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RestrictionController extends Controller
{

    use Translations;

    public function __construct()
    {
        $this->middleware('permission:restrictions.read')->only('index');
        $this->middleware('permission:restrictions.create')->only('store');
        $this->middleware('permission:restrictions.update')->only('update');
        $this->middleware('permission:restrictions.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $client_id = $this->getClientId($request);
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $restriction = Restriction::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'restriction');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'client_restrictions' => function ($query) use ($client_id) {
                $query->select('id', 'restriction_id', 'client_id');
                $query->where('client_id', $client_id);
            }
        ]);

        if (!empty($client_id)) {
            $restriction = $restriction->whereHas('client_restrictions', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $restriction = $restriction->whereDoesntHave('client_restrictions');
        }

        $count = $restriction->count();
        if ($querySearch) {
            $restriction->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'restriction');
                    $query->where('value', 'like', '%' . $querySearch . '%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }

        if ($paging === 1) {
            $restriction = $restriction->take($limit)->get();
        } else {
            $restriction = $restriction->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $restriction,
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
        $client_id = $this->getClientId($request);

        $validate = [];
        if (empty($client_id)) {
            $validate = [
                'translations.*.requirement_name' => 'unique:translations,value,NULL,id,type,requirement'
            ];
        }

        $validator = Validator::make($request->all(), $validate);

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
            $restriction = new Restriction();
            $restriction->save();

            if (!empty($client_id)) {
                $newClientService = new ClientRestriction();
                $newClientService->client_id = $client_id;
                $newClientService->restriction_id = $restriction->id;
                $newClientService->save();
            }

            $this->saveTranslation($request->input("translations"), 'restriction', $restriction->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Restriction $restriction
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $restriction = Restriction::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'restriction');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $restriction]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Restriction $restriction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $client_id = $this->getClientId($request);

        $validate = [];
        if (empty($client_id)) {
            $validate = [
                'translations.*.restriction_name' => 'unique:translations,value,' . $id . ',object_id,type,restriction'
            ];
        }

        $validator = Validator::make($request->all(), $validate);

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
            $restriction = Restriction::find($id);
            $restriction->save();
            $this->saveTranslation($request->input("translations"), 'restriction', $restriction);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Restriction $restriction
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $restriction_used = RestrictionService::where('restriction_id', $id)->get();
        if ($restriction_used->count() === 0) {
            $restriction = Restriction::find($id);
            $restriction->delete();
            $this->deleteTranslation('restriction', $id);
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
        $restrictions = Restriction::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'restriction');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $restrictions]);
    }

    public function selectBoxList(Request $request)
    {
        $lang = $request->has('lang') ? $request->input('lang') : 'es';
        $language = Language::where('iso', $lang)->first();

        $restrictions = collect();

        $restrictions_query = Restriction::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'restriction');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $restrictions_query->transform(function($query) use ($restrictions){
            $restrictions->add([
                'id' => $query->id,
                'name' => ($query->translations->count() > 0) ? $query->translations[0]['value'] : '',
            ]);
        });

        return Response::json(['success' => true, 'data' => $restrictions]);
    }
}
