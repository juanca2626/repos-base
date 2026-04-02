<?php

namespace App\Http\Controllers;

use App\Language;
use App\Http\Traits\Translations;
use App\TypeClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TypesClassController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:typesclass.read')->only('index');
        $this->middleware('permission:typesclass.create')->only('store');
        $this->middleware('permission:typesclass.update')->only('update');
        $this->middleware('permission:typesclass.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $typesclass = TypeClass::where("code", "!=", "X")->where("code", "!=", "x")
            ->with([
                'translations' => function ($query) use ($lang) {
                    $query->where('type', 'typeclass');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->orderBy('order', 'asc')->get();
        return Response::json(['success' => true, 'data' => $typesclass]);
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
            'translations.*.typeclass_name' => 'unique:translations,value,NULL,id,type,typeclass'
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
            $typesclass = new TypeClass();
            $typesclass->save();
            $this->saveTranslation($request->input("translations"), 'typeclass', $typesclass->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $typesclass = TypeClass::with('translations')->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $typesclass]);
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
            'order' => 'required',
            'translations.*.typeclass_name' => 'unique:translations,value,' . $id . ',object_id,type,typeclass'
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
            $typeClass = TypeClass::where('id', '=', $id)->first();
            $typeClass->order = $request->input('order');
            $typeClass->save();

            $this->saveTranslation($request->input('translations'), 'typeclass', $id);
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
        $typesclass = TypeClass::find($id);

        $typesclass->delete();

        $this->deleteTranslation('typeclass', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $typesclass = TypeClass::with([
            'hotels',
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'typeclass');
                $query->where('language_id', $language->id);

            }
        ])->whereHas('hotelclass', function ($query) {
            $query->where('status', '=', 1);
            $query->whereNull('deleted_at');
        })->orderBy('order', 'ASC')->get();

        $typesclass->map(function ($item) {
            $item["checked"] = false;
        });
        if ($request->input('type') == 2) {
            $typesclass = $typesclass->filter(function ($item) {
                return $item->code != 'X' && $item->code != 'x';
            });
        }
        return Response::json(['success' => true, 'data' => $typesclass]);
    }

    public function selectBoxQuote(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $typesclass = TypeClass::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'typeclass');
                $query->where('language_id', $language->id);

            }
        ])->orderBy('order', 'ASC')->get(['id','code','color','color']);

        $typesclass->map(function ($item) {
            $item["checked"] = false;
        });

        if ($request->input('type') == 2) {
            $typesclass = $typesclass->filter(function ($item) {
                return $item->code != 'X' && $item->code != 'x';
            });
        }
        return Response::json(['success' => true, 'data' => $typesclass]);
    }

    public function selectBox2(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $type_classes = TypeClass::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'typeclass');
                $query->where('language_id', $language->id);
            }
        ])->get();
        $result = [];
        foreach ($type_classes as $type_class) {
            array_push($result, ['label' => $type_class["translations"][0]["value"], 'code' => $type_class["id"]]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function selectBoxForAllotment(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $typesclass = TypeClass::with([          
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'typeclass');
                $query->where('language_id', $language->id);

            }
        ])->whereHas('hotelclass', function ($query) {
            $query->where('status', '=', 1);
            $query->whereNull('deleted_at');
        })->select('id','code', 'color','order')
        ->orderBy('order', 'ASC')->get();

        $typesclass->map(function ($item) {
            $item["checked"] = false;
        });
        $typesclass = $typesclass->filter(function ($item) {
                return  $item->id != '8' ;
        });

        return Response::json(['success' => true, 'data' => $typesclass]);
    }
        
}
