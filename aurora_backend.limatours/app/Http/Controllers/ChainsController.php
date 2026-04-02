<?php

namespace App\Http\Controllers;

use App\Chain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ChainsController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:chains.read')->only('index');
        $this->middleware('permission:chains.create')->only('store');
        $this->middleware('permission:chains.update')->only('update');
        $this->middleware('permission:chains.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        $chains = Chain::all();
        return Response::json(['success' => true, 'data' => $chains]);
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
            'name' => 'required|unique:chains,name'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $chains = new Chain();
            $chains->name = $request->input('name');
            $chains->status = $request->input('status');
            $chains->save();

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
        $chains = Chain::find($id);

        return Response::json(['success' => true, 'data' => $chains]);
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
            'name' => 'required|unique:chains,name,' . $id
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $chains = Chain::find($id);
            $chains->name = $request->input('name');
            $chains->status = $request->input('status');
            $chains->save();
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
        $chains = Chain::find($id);

        $chains->delete();

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $chains = Chain::select('id', 'name')->orderBy('name', 'ASC')->get();
        $result = [];
        foreach ($chains as $chain) {
            array_push($result, ['label' => $chain->name, 'code' => $chain->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function updateStatus($id, Request $request)
    {
        $chain = Chain::find($id);
        if ($request->input("status")) {
            $chain->status = false;
        } else {
            $chain->status = true;
        }
        $chain->save();
        return Response::json(['success' => true]);
    }
}
