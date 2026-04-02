<?php

namespace App\Http\Controllers;

use App\ComponentSubstitute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ComponentSubstitutesController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:servicecomponents.read')->only('index');
        $this->middleware('permission:servicecomponents.create')->only('store');
        $this->middleware('permission:servicecomponents.update')->only('update');
        $this->middleware('permission:servicecomponents.delete')->only('delete');
    }

    public function index($component_id)
    {

        $service_subs = ComponentSubstitute::with([
            'service' => function ($query) {
                $query->with([
                    'serviceType' => function ($query) {
                        $query->with([
                            'translations' => function ($query) {
                                $query->select('object_id', 'value');
                                $query->where('type', 'servicetype');
                                $query->where('language_id', 1);
                            },
                        ]);
                    }
                ]);
            }
        ])
            ->where('component_id', $component_id)
            ->get();


        $data = [
            'data' => $service_subs,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param $component_id
     * @param Request $request
     * @return JsonResponse
     */
    public function store($component_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required'
        ]);
        try {
            if ($validator->fails()) {
                $response = ['success' => false];
            } else {

                $service_id = $request->input("service_id");

                $serviceComponent_count = ComponentSubstitute::where('service_id', $service_id)
                    ->where("component_id", $component_id)
                    ->count();
                if ($serviceComponent_count>0) {
                    $response = ['success' => false, 'error' => 'ALREADY_EXISTS'];
                    return Response::json($response);
                }

                $substitute = new ComponentSubstitute();
                $substitute->component_id = $component_id;
                $substitute->service_id = $service_id;
                $substitute->save();
                $response = ['success' => true, 'object_id' => $substitute->id];
            }
            return Response::json($response);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    /**
     * @param $component_id
     * @param $id
     * @return JsonResponse
     */
    public function destroy($component_id, $id)
    {
        $component = ComponentSubstitute::find($id);
        $component->delete();
        return Response::json(['success' => true]);
    }

}
