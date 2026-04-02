<?php

namespace App\Http\Controllers;

use App\TrainRailRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainRailRoutesController extends Controller
{

    public function getByTrain($train_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');

        $rail_route = TrainRailRoute::where('train_id',$train_id);

        if ($querySearch) {
            $rail_route->where('name', 'like', '%' . $querySearch . '%');
        }

        $count = $rail_route->count();

        if ($paging === 1) {
            $rail_route = $rail_route->take($limit)->orderBy('created_at', 'desc')->get();
        } else {
            $rail_route = $rail_route->skip($limit * ($paging - 1))->orderBy('created_at', 'desc')
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
        $train_id = $request->input('train_id');
        $rail_route_id = $request->input('rail_route_id');
        $code = $request->input('code');
        $abbreviation = $request->input('abbreviation');

        $rail_route = new TrainRailRoute;
        $rail_route->name = $name;
        $rail_route->train_id = $train_id;
        $rail_route->rail_route_id = $rail_route_id;
        $rail_route->code = $code;
        $rail_route->abbreviation = $abbreviation;
        if( $rail_route->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }


    public function update( $id, Request $request)
    {
        $name = $request->input('name');
        $train_id = $request->input('train_id');
        $rail_route_id = $request->input('rail_route_id');
        $code = $request->input('code');
        $abbreviation = $request->input('abbreviation');

        $rail_route = TrainRailRoute::find($id);
        $rail_route->name = $name;
        $rail_route->train_id = $train_id;
        $rail_route->rail_route_id = $rail_route_id;
        $rail_route->code = $code;
        $rail_route->abbreviation = $abbreviation;
        if( $rail_route->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }


    public function destroy($id)
    {
        $rail_route = TrainRailRoute::find($id);

        $rail_route->delete();

        return Response::json(['success' => true]);

    }


}
