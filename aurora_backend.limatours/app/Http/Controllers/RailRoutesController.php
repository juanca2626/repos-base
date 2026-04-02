<?php

namespace App\Http\Controllers;

use App\RailRoute;
use App\TrainRailRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RailRoutesController extends Controller
{

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');

        if( $request->input('status') ){
            $rail_route = RailRoute::where('status',$request->input('status'));
        } else {
            $rail_route = RailRoute::whereIn('status',[1,0]);
        }

        if ($querySearch) {
            $rail_route->where('name', 'like', '%' . $querySearch . '%');
        }

        $count = $rail_route->count();

        if ($paging === 1) {
            $rail_route = $rail_route->take($limit)->orderBy('status', 'desc')->get();
        } else {
            $rail_route = $rail_route->skip($limit * ($paging - 1))->orderBy('status', 'desc')
                ->take($limit)->get();
        }

        $data = [
            'data' => $rail_route,
            'count' => $count,
        ];
        return Response::json($data);
    }

    public function store(Request $request)
    {
        $name = $request->input('name');

        $rail_route = new RailRoute;
        $rail_route->name = $name;
        $rail_route->status = 1;
        if( $rail_route->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }

    public function update( $id, Request $request)
    {
        $name = $request->input('name');

        $rail_route = RailRoute::find($id);
        $rail_route->name = $name;
        if( $rail_route->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }

    public function destroy($id)
    {
        $rail_route = RailRoute::find($id);

        $train_rail_routes_count = TrainRailRoute::where('rail_route_id', $id)->count();

        if( $train_rail_routes_count == 0 ){
            $rail_route->delete();

            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, 'message'=>"Existen rutas asociadas, por favor primero validar"]);
        }

    }

    public function updateStatus($id, Request $request)
    {
        $train = RailRoute::find($id);
        if ($request->input("status")) {
            $train->status = 1;
        } else {
            $train->status = 0;
        }
        $train->save();
        return Response::json(['success' => true]);
    }


}
