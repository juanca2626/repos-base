<?php

namespace App\Http\Controllers;

use App\TrainInclusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TrainInclusionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $train_template_id)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        if (!empty($train_template_id)) {
            $inclusion = TrainInclusion::with([
                'inclusions.translations' => function ($query) use ($lang) {
                    $query->where('type', 'inclusion');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->where('train_template_id', $train_template_id);
            $count = $inclusion->count();
            if ($querySearch) {
                $inclusion->where(function ($query) use ($querySearch) {
                    $query->orWhere('name', 'like', '%' . $querySearch . '%');
                });
            }

            if ($paging === 1) {
                $inclusion = $inclusion->take($limit)->get();
            } else {
                $inclusion = $inclusion->skip($limit * ($paging - 1))->take($limit)->get();
            }
        } else {
            $inclusion = [];
            $count = 0;
        }

        $data = [
            'data' => $inclusion,
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
    public function store(Request $request, $train_template_id)
    {
        $validator = Validator::make($request->all(), [
            'inclusion_id' => 'required',
            'include' => 'required',
            'see_client' => 'required'
        ]);

        $validateInclusion = TrainInclusion::where('train_template_id', $train_template_id)
            ->where('inclusion_id', $request->input('inclusion_id'))
            ->count();

        if ($validator->fails() or $validateInclusion > 0) {
            return Response::json(['success' => false, 'error' => 'validation_duplicate']);
        } else {
            $trainInclusion = new TrainInclusion();
            $trainInclusion->train_template_id = $train_template_id;
            $trainInclusion->inclusion_id = $request->input('inclusion_id');
            $trainInclusion->include = $request->input('include');
            $trainInclusion->see_client = $request->input('see_client');
            $trainInclusion->save();

            return Response::json(['success' => true]);
        }
    }

    public function updateStatus($id, Request $request)
    {
        $trainInclusion = TrainInclusion::find($id);
        if ($request->input("include")) {
            $trainInclusion->include = false;
        } else {
            $trainInclusion->include = true;
        }
        $trainInclusion->save();
        return Response::json(['success' => true]);
    }

    public function updateStatusSeeClient($id, Request $request)
    {
        $trainInclusion = TrainInclusion::find($id);
        if ($request->input("see_client")) {
            $trainInclusion->see_client = false;
        } else {
            $trainInclusion->see_client = true;
        }
        $trainInclusion->save();
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\InclusionService $inclusionService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $service = TrainInclusion::find($id);
            $service->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }
}
