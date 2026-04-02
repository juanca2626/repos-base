<?php

namespace App\Http\Controllers;

use App\Inclusion;
use App\Language;
use App\Mail\NotificationInclusionService;
use App\ProgressBar;
use App\Service;
use App\ServiceInclusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceInclusionController extends Controller
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
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso',$lang)->first();
        if (!empty($service_id)) {
            $inclusion = ServiceInclusion::with([
                'inclusions.translations' => function ($query) use ($language) {
                    $query->where('type', 'inclusion');
                    $query->where('language_id', $language->id);
                }
            ])->where('service_id', $service_id);

            if ($querySearch) {
                $inclusion->where(function ($query) use ($querySearch) {
                    $query->orWhere('name', 'like', '%'.$querySearch.'%');
                });
            }
            $count = $inclusion->count();
            if ($paging === 1) {
                $inclusion = $inclusion->take($limit)->orderBy('day')->get();
            } else {
                $inclusion = $inclusion->skip($limit * ($paging - 1))->take($limit)->orderBy('day')->orderBy('order')->get();
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inclusion_id' => 'required',
            'service_id' => 'required',
            'include' => 'required',
            'see_client' => 'required',
            'day' => 'required'
        ]);

        $validateInclusion = ServiceInclusion::where('service_id', $request->input('service_id'))
            ->where('inclusion_id', $request->input('inclusion_id'))
            ->where('day', $request->input('day'))
            ->count();
        if ($validator->fails() or $validateInclusion > 0) {
            return Response::json(['success' => false, 'error' => 'validation_duplicate']);
        } else {

            $serviceInclusionOrder =ServiceInclusion::where('service_id', $request->input('service_id'))
                ->where('day', $request->input('day'))
                ->orderBy('day')
                ->orderBy('order')
                ->max('order');
            $serviceInclusion = new ServiceInclusion();
            $serviceInclusion->service_id = $request->input('service_id');
            $serviceInclusion->inclusion_id = $request->input('inclusion_id');
            $serviceInclusion->include = $request->input('include');
            $serviceInclusion->see_client = $request->input('see_client');
            $serviceInclusion->day = $request->input('day');
            $serviceInclusion->order = $serviceInclusionOrder + 1;
            $serviceInclusion->save();

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0)
            ) {
                $inclusion_dirty = [];
                $inclusion_dirty['inclusion_id'] = $serviceInclusion->inclusion_id;
                $inclusion_dirty['include'] = $serviceInclusion->include;
                $inclusion_dirty['see_client'] = $serviceInclusion->see_client;
                $inclusion_dirty['day'] = $serviceInclusion->day;
                $service = Service::find($request->input('service_id'));
                $this->buildDataNotification(
                    'create',
                    $service->id,
                    $service->aurora_code,
                    $request->input('emails'),
                    $request->input('message'),
                    $inclusion_dirty
                );
            }
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_inclusions',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $serviceInclusion->service_id
                ]
            );
            return Response::json(['success' => true]);
        }
    }

    public function buildDataNotification(
        $action,
        $service_id,
        $aurora_code,
        $emails,
        $message,
        $inclusions
    ) {
        if (count($inclusions) > 0) {
            if (isset($inclusions['inclusion_id'])) {
                $activity = Inclusion::where('id', $inclusions['inclusion_id'])
                    ->with([
                        'translations' => function ($query) {
                            $query->where('type', 'inclusion');
                            $query->where('language_id', 1);
                        }
                    ])->first();
                $inclusions['inclusion_id'] = $activity->translations[0]->value;
            }
        }

        $data = [
            'action' => $action,
            'service_id' => $service_id,
            'aurora_code' => $aurora_code,
            'inclusions' => $inclusions,
            'message' => $message,
        ];
        Mail::to($emails)->send(new NotificationInclusionService($data));
    }

    public function updateStatus($id, Request $request)
    {
        $serviceInclusion = ServiceInclusion::find($id);
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
        $serviceInclusion = ServiceInclusion::find($id);
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
     * @param  \App\InclusionService  $inclusionService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $service = ServiceInclusion::find($id);
            $service->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function upService(Request $request)
    {
        $service_inclusion_id = $request->input('service_inclusion_id');
        $order = $request->input('order');

        $service_up = ServiceInclusion::find($service_inclusion_id);

        $service_down = ServiceInclusion::where('service_id',$service_up->service_id)->where('day',$service_up->day)->where('order','<',$order)->orderBy('order','desc')->first();

        if ($service_down != null)
        {
            $service_up->order = $service_down->order;
            $service_up->save();

            $service_down_updated = ServiceInclusion::find($service_down->id);
            $service_down_updated->order = $order;
            $service_down_updated->save();
        }

        return \response()->json("orden de inclusion del servicio actualizada");
    }
    public function downService(Request $request)
    {
        $service_inclusion_id = $request->input('service_inclusion_id');
        $order = $request->input('order');

        $service_up = ServiceInclusion::find($service_inclusion_id);

        $service_down = ServiceInclusion::where('service_id',$service_up->service_id)->where('day',$service_up->day)->where('order','>',$order)->orderBy('order','asc')->first();

        if ($service_down != null)
        {
            $service_up->order = $service_down->order;
            $service_up->save();

            $service_down_updated = ServiceInclusion::find($service_down->id);
            $service_down_updated->order = $order;
            $service_down_updated->save();
        }

        return \response()->json("orden de inclusion del servicio actualizada");
    }
}
