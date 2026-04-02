<?php

namespace App\Http\Controllers;

use App\Language;
use App\PackageInclusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageInclusionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $package_id)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        if (!empty($package_id)) {
            $inclusion = PackageInclusion::with([
                'inclusions.translations' => function ($query) use ($language) {
                    $query->where('type', 'inclusion');
                    $query->where('language_id', $language->id);
                }
            ])->where('package_id', $package_id);
            $count = $inclusion->count();
            if ($querySearch) {
                $inclusion->where(function ($query) use ($querySearch) {
                    $query->orWhere('name', 'like', '%'.$querySearch.'%');
                });
            }

            if ($paging === 1) {
                $inclusion = $inclusion->take($limit)->orderBy('id')->get();
            } else {
                $inclusion = $inclusion->skip($limit * ($paging - 1))->take($limit)->orderBy('id')->get();
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
        $validator = Validator::make($request->all(), [
            'inclusion_id' => 'required',
            'package_id' => 'required',
            'include' => 'required',
            'see_client' => 'required',
        ]);

        $validateInclusion = PackageInclusion::where('package_id', $request->input('package_id'))
            ->where('inclusion_id', $request->input('inclusion_id'))
            ->count();
        if ($validator->fails() or $validateInclusion > 0) {
            return Response::json(['success' => false, 'error' => 'validation_duplicate']);
        } else {
            $serviceInclusion = new PackageInclusion();
            $serviceInclusion->package_id = $request->input('package_id');
            $serviceInclusion->inclusion_id = $request->input('inclusion_id');
            $serviceInclusion->include = $request->input('include');
            $serviceInclusion->see_client = $request->input('see_client');
            $serviceInclusion->save();

//            if (($request->has('hasNotify') and $request->input('hasNotify')) and
//                ($request->has('emails') and count($request->input('emails')) > 0)
//            ) {
//                $inclusion_dirty = [];
//                $inclusion_dirty['inclusion_id'] = $serviceInclusion->inclusion_id;
//                $inclusion_dirty['include'] = $serviceInclusion->include;
//                $inclusion_dirty['see_client'] = $serviceInclusion->see_client;
//                $inclusion_dirty['day'] = $serviceInclusion->day;
//                $service = Package::find($request->input('service_id'));
//                $this->buildDataNotification(
//                    'create',
//                    $service->id,
//                    $service->aurora_code,
//                    $request->input('emails'),
//                    $request->input('message'),
//                    $inclusion_dirty
//                );
//            }
//            ProgressBar::updateOrCreate(
//                [
//                    'slug' => 'service_progress_inclusions',
//                    'value' => 10,
//                    'type' => 'service',
//                    'object_id' => $serviceInclusion->service_id
//                ]
//            );
            return Response::json(['success' => true]);
        }
    }

    public function updateStatus($id, Request $request)
    {
        $serviceInclusion = PackageInclusion::find($id);
        if ($request->input("include")) {
            $serviceInclusion->include = 0;
        } else {
            $serviceInclusion->include = 1;
        }
        $serviceInclusion->save();
        return Response::json(['success' => true]);
    }

    public function updateStatusSeeClient($id, Request $request)
    {
        $serviceInclusion = PackageInclusion::find($id);
        if ($request->input("see_client")) {
            $serviceInclusion->see_client = 0;
        } else {
            $serviceInclusion->see_client = 1;
        }
        $serviceInclusion->save();
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PackageInclusion  $packageInclusion
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $service = PackageInclusion::find($id);
            $service->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }
}
