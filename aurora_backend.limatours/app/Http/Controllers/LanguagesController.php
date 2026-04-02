<?php

namespace App\Http\Controllers;

use App\Language;
use App\Http\Traits\Images;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class LanguagesController extends Controller
{
    use Images;

    public function __construct()
    {
        $this->middleware('permission:languages.read')->only('getLanguagesActives');
        $this->middleware('permission:languages.create')->only('store');
        $this->middleware('permission:languages.update')->only('update');
        $this->middleware('permission:languages.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $languages = Language::all()->where('state','=', 1);
        return Response::json(['success' => true, 'data' => $languages]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function getLanguagesActives()
    {
        $languages = Language::all()->where('state','=', 1);
        return Response::json(['success' => true, 'data' => $languages]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function allLanguage()
    {
        $languages = Language::all();
        return Response::json(['success' => true, 'data' => $languages]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:languages,name',
            'iso' => 'required|unique:languages,iso',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $language = new Language();
            $language->name = $request->input('name');
            $language->iso = $request->input('iso');
            $language->state = $request->input('status');
            $language->save();

            $this->imagesSave(Auth::user()->id, 'languages', $language->id);

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
        $language = Language::find($id);

        return Response::json([
            'success' => true,
            'data' => $language,
            'image' => $this->imagesExists('languages', $language->id)
        ]);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:languages,name,' . $id,
            'iso' => 'required|unique:languages,iso,' . $id,
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $language = Language::find($id);
            $language->name = $request->input('name');
            $language->iso = $request->input('iso');
            $language->state = $request->input('status');
            $language->save();

            $this->imagesSave(Auth::user()->id, 'languages', $language->id);

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
        $languages = Language::find($id);

        $languages->delete();

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $languages = Language::select('id', 'name')->get();
        $result = [];
        foreach ($languages as $language) {
            array_push($result, ['text' => $language->name, 'value' => $language->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function uploadImage(Request $request)
    {
        $response = [
            'success' => false,
            'name' => '',
            'message' => ''
        ];

        if ($request->file('file')) {
            $response = $this->imagesSaveTmp(Auth::user()->id, 'languages', $request);
        } else {
            $response['message'] = "File didnt upload";
        }

        return Response::json($response);
    }

    public function removeImage($id)
    {
        $this->imagesRemove('languages', $id);

        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $amenity = Language::find($id);
        if ($request->input("state")) {
            $amenity->state = false;
        } else {
            $amenity->state = true;
        }
        $amenity->save();
        return Response::json(['success' => true]);
    }
    public function getArrayLanguages()
    {
        $languages = Language::where('state',1)->get();
        $array_languages = [];
        foreach ($languages as $language) {
            array_push($array_languages, ["slug" => $language->iso,"language_id" => $language->id, "translation" =>""]);
        }

        return response()->json($array_languages, 200);
    }
}
