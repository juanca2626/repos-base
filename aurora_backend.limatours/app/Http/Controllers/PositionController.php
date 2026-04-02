<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $data = Position::get(['id', 'name']);
        return Response::json(['success' => true, 'data' => $data, 'count' => $data->count()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $name = $request->post('name');
            $newPosition = new Position();
            $newPosition->name = $name;
            $newPosition->save();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $find_position = Position::find($id);
        if ($find_position) {
            return Response::json(['success' => true, 'data' => $find_position]);
        }
        return Response::json(['success' => false]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $find_position = Position::find($id);
        if ($find_position) {
            $find_position->name = $request->input('name');
            $find_position->save();
            return Response::json(['success' => true]);
        }
        return Response::json(['success' => false]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $subcategory_used = Employee::where('position_id', $id)->take(1)->get();
        if ($subcategory_used->count() == 0) {
            $find_position = Position::find($id);
            $find_position->delete();
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }
}
