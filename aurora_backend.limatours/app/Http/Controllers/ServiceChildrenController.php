<?php

namespace App\Http\Controllers;

use App\Service;
use App\ServiceChild;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServiceChildrenController extends Controller
{
    use Translations;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($service_id, Request $request)
    {
        $children = ServiceChild::where('service_id', $service_id)->get();
        $data = [
            'data' => $children,
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
    public function store($service_id, Request $request)
    {
        $service = Service::find($service_id);
        $service->allow_child = 1;
        $service->save();

        $serviceChild = new ServiceChild();
        $serviceChild->min_age = $request->input('child_min_age');
        $serviceChild->max_age = $request->input('child_max_age');
        $serviceChild->status = 1;
        $serviceChild->service_id = $service_id;
        $serviceChild->save();
        $response = ['success' => true, 'object_id' => $serviceChild->id];
        return Response::json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($service_id, $child_id)
    {
        $child = ServiceChild::find($child_id);
        $child->delete();
        return Response::json(['success' => true]);
    }

    public function updateStatus($service_id, $child_id, Request $request)
    {
        $child = ServiceChild::find($child_id);
        if ($request->input("status")) {
            $child->status = false;
        } else {
            $child->status = true;
        }
        $child->save();
        return Response::json(['success' => true]);
    }
}
