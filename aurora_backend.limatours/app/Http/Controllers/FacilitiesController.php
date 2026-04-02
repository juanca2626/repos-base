<?php

namespace App\Http\Controllers;

use App\Facility;
use App\Http\Traits\Images;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class FacilitiesController extends Controller
{
    use Translations, Images;

    public function __construct()
    {
        $this->middleware('permission:facilities.read')->only('index');
        $this->middleware('permission:facilities.create')->only('store');
        $this->middleware('permission:facilities.update')->only('update');
        $this->middleware('permission:facilities.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $facilities = Facility::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'facility');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'facility');
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $facilities]);
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
            'translations.*.facility_name' => 'unique:translations,value,NULL,id,type,facility'
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
            $facility = new Facility();
            $facility->status = $request->input('status');
            $facility->save();

            $this->saveTranslation($request->input("translations"), 'facility', $facility->id);

            return Response::json(['success' => true, 'object_id' => $facility->id]);
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
        $facility = Facility::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'facility');
                $query->where('object_id', $id);
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'facility');
            }
        ])->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $facility]);
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
            'translations.*.facility_name' => 'unique:translations,value,' . $id . ',object_id,type,facility'
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
            $facility = Facility::find($id);
            $facility->status = $request->input('status');
            $facility->save();

            $this->saveTranslation($request->input('translations'), 'facility', $id);

            return Response::json(['success' => true, 'object_id' => $facility->id]);
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
        $facility = Facility::find($id);

        $facility->delete();

        $this->deleteTranslation('facility', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $facilities = Facility::all();
        return Response::json(['success' => true, 'data' => $facilities]);
    }

    public function changeStatus($id, Request $request)
    {
        $facility = Facility::find($id);
        if ($request->input("state")) {
            $facility->status = false;
        } else {
            $facility->status = true;
        }
        $facility->save();
        return Response::json(['success' => true]);
    }

}
