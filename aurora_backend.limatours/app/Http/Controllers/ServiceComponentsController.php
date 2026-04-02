<?php

namespace App\Http\Controllers;

use App\Component;
use App\ComponentClient;
use App\ComponentSubstitute;
use App\Http\Traits\ServiceComponent as TraitServiceComponent;
use App\ServiceComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceComponentsController extends Controller
{

    use TraitServiceComponent;

    public function __construct()
    {
        $this->middleware('permission:servicecomponents.read')->only('index');
        $this->middleware('permission:servicecomponents.create')->only('store');
        $this->middleware('permission:servicecomponents.update')->only('update');
        $this->middleware('permission:servicecomponents.delete')->only('delete');
    }

    public function index($service_id, Request $request)
    {

        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $service_component = ServiceComponent::where('service_id', $service_id)->get()->first();
        $serviceComponents = [];
        $count = 0;

        if ($service_component) {
            $serviceComponents = Component::with([
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
                ->withCount('substitutes')
                ->where('service_component_id', $service_component->id);

            $count = $serviceComponents->count();

            if ($querySearch) {
                $serviceComponents->where(function ($query) use ($querySearch) {
                    $query->orWhereHas('service', function ($q) use ($querySearch) {

                        $q->where('name', 'like', '%' . $querySearch . '%');
                    });
                });
            }

            if ($paging === 1) {
                $serviceComponents = $serviceComponents->take($limit)->get();
            } else {
                $serviceComponents = $serviceComponents->skip($limit * ($paging - 1))->take($limit)->get();
            }
        }

        $data = [
            'data' => $serviceComponents,
            'count' => $count,
            'max_nights' => $this->max_nights( $service_id ),
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param $service_id
     * @param Request $request
     * @return JsonResponse
     */
    public function store($service_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'component_id' => 'required',
            'service_id' => 'required'
        ]);
        try {
            if ($validator->fails()) {
                $response = ['success' => false];
            } else {
                DB::beginTransaction();
                $serviceComponent = ServiceComponent::where('service_id', $service_id)->get()->first();
                if (!$serviceComponent) {
                    $serviceComponent = new ServiceComponent();
                    $serviceComponent->service_id = $service_id;
                    $serviceComponent->save();
                }
                $component = new Component();
                $component->service_component_id = $serviceComponent->id;
                $component->service_id = $request->input('component_id');
                $component->save();
                $response = ['success' => true, 'object_id' => $component->id];
                DB::commit();
            }
            return Response::json($response);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function update($component_id, Request $request){

        $data_component = $request->input('data');

        $component = Component::find($component_id);
        $component->lock = $data_component['lock'];
        $component->after_days = $data_component['after_days'];
        $component->save();

        $response = ['success' => true, 'object_id' => $component->id];
        return Response::json($response);
    }

    /**
     * @param $service_id
     * @param $component_id
     * @return JsonResponse
     */
    public function destroy($service_id, $component_id)
    {
        $component = Component::find($component_id);
        $component->delete();
        return Response::json(['success' => true]);
    }

    public function get_max_nights($service_id)
    {

        $max_nights = $this->max_nights( $service_id );

        return Response::json(['data' => $max_nights]);
    }

    public function store_component_client($component_id, Request $request)
    {

        $service_id = $request->input('service_id');
        $client_id = $request->input('client_id');

        $component = Component::find($component_id);

        ComponentClient::where('client_id', $client_id)->where('component_id', $component_id)->delete();
        if( $component->service_id !== $service_id ){
            $new_component_client = new ComponentClient;
            $new_component_client->client_id = $client_id;
            $new_component_client->component_id = $component_id;
            $new_component_client->service_id = $service_id;
            $new_component_client->save();
        }

        return Response::json(['success' => true]);
    }

    public function copy_component($service_id,$component_id)
    {

        $component = Component::where('id',$component_id)->with('substitutes')->first();

        $max_nights = $this->max_nights( $service_id );

        $total_components_same = Component::where('service_component_id', $component->service_component_id)
            ->where('service_id', $component->service_id)->count();

        if( $total_components_same >= $max_nights ){
            return Response::json(['success' => false, 'error' => 'MAX_NIGHTS',
                '$total_components_same'=> $total_components_same, '$max_nights'=> $max_nights]);
        }

        $new_component = new Component;
        $new_component->service_id = $component->service_id;
        $new_component->service_component_id = $component->service_component_id;
        $new_component->lock = $component->lock;
        $new_component->after_days = $total_components_same;
        $new_component->save();

        foreach( $component->substitutes as $substitute ){
            $new_substitute = new ComponentSubstitute();
            $new_substitute->component_id = $new_component->id;
            $new_substitute->service_id = $substitute->service_id;
            $new_substitute->save();
        }

        return Response::json(['success' => true,
            '$total_components_same'=> $total_components_same, '$max_nights'=> $max_nights]);
    }

}
