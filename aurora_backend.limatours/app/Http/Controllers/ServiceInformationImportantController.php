<?php

namespace App\Http\Controllers;

use App\Language;
use App\Service;
use App\ServiceInformationImportant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceInformationImportantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $service_id)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $lang = $request->input('lang');
        $language = Language::where('iso',$lang)->first();
        if (!empty($service_id)) {
            $service_featured = ServiceInformationImportant::with([
                'featured.translations' => function ($query) use ($language) {
                    $query->where('type', 'information_important_service');
                    $query->where('language_id', $language->id);
                }
            ])->where('service_id', $service_id);

            $count = $service_featured->count();

            if ($paging === 1) {
                $service_featured = $service_featured->take($limit)->orderBy('id')->get();
            } else {
                $service_featured = $service_featured->skip($limit * ($paging - 1))->take($limit)->orderBy('id')->get();
            }
        } else {
            $service_featured = [];
            $count = 0;
        }

        $data = [
            'data' => $service_featured,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
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
            'info_important_service_id' => 'required',
            'service_id' => 'required',
        ]);

        $validateInclusion = ServiceInformationImportant::where('service_id', $request->input('service_id'))
            ->where('info_important_service_id', $request->input('info_important_service_id'))
            ->count();
        if ($validator->fails() or $validateInclusion > 0) {
            return Response::json(['success' => false, 'error' => 'validation_duplicate']);
        } else {
            $serviceInclusion = new ServiceInformationImportant();
            $serviceInclusion->service_id = $request->input('service_id');
            $serviceInclusion->info_important_service_id = $request->input('info_important_service_id');
            $serviceInclusion->save();
            return Response::json(['success' => true]);
        }
    }


    public function destroy($id)
    {
        try {
            $service = ServiceInformationImportant::find($id);
            $service->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }
}
