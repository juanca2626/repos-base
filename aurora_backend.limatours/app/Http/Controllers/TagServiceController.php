<?php

namespace App\Http\Controllers;

use App\TagService;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TagServiceController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:tagservices.create')->only('store');
        $this->middleware('permission:tagservices.update')->only('update');
        $this->middleware('permission:tagservices.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $tagservices = TagService::with([
                'translations' => function ($query) use ($lang) {
                    $query->where('type', 'tagservices');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();
        return Response::json(['success' => true, 'data' => $tagservices]);
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
            'translations.*.tagservices_name' => 'unique:translations,value,NULL,id,type,tagservices'
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
            $tagservices = new TagService();
            $tagservices->save();

            $this->saveTranslation($request->input('translations_name'), 'tagservices',  $tagservices->id);
            $this->saveTranslation($request->input('translations_description'), 'tagservices',  $tagservices->id);
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
        $tagservices = TagService::with('translations')->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $tagservices]);
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
            'translations.*.tagservices_name' => 'unique:translations,value,' . $id . ',object_id,type,tagservices'
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
            $this->saveTranslation($request->input('translations_name'), 'tagservices', $id);
            $this->saveTranslation($request->input('translations_description'), 'tagservices', $id);
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
        $tagservices = TagService::find($id);

        $tagservices->delete();

        $this->deleteTranslation('tagservices', $id);

        return Response::json(['success' => true]);
    }
}
