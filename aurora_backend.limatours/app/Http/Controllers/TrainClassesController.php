<?php

namespace App\Http\Controllers;

use App\TrainClass;
use App\TrainTrainClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainClassesController extends Controller
{

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');

        $train_class = TrainClass::whereIn('status',[1,0]);

        if ($querySearch) {
            $train_class->where('name', 'like', '%' . $querySearch . '%');
        }

        $count = $train_class->count();

        if ($paging === 1) {
            $train_class = $train_class->take($limit)->orderBy('status', 'desc')->get();
        } else {
            $train_class = $train_class->skip($limit * ($paging - 1))->orderBy('status', 'desc')
                ->take($limit)->get();
        }

        $data = [
            'data' => $train_class,
            'count' => $count,
        ];
        return Response::json($data);
    }

    public function store(Request $request)
    {
        $name = $request->input('name');

        $train_class = new TrainClass;
        $train_class->name = $name;
        $train_class->status = 1;
        if( $train_class->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }

    public function update( $id, Request $request)
    {
        $name = $request->input('name');

        $train_class = TrainClass::find($id);
        $train_class->name = $name;
        if( $train_class->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }

    public function destroy($id)
    {
        $train_class = TrainClass::find($id);

        $train_train_class_count = TrainTrainClass::where('train_class_id', $id)->count();

        if( $train_train_class_count == 0 ){
            $train_class->delete();

            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, 'message'=>"Existen rutas asociadas, por favor primero validar"]);
        }

    }

    public function updateStatus($id, Request $request)
    {
        $train = TrainClass::find($id);
        if ($request->input("status")) {
            $train->status = 1;
        } else {
            $train->status = 0;
        }
        $train->save();
        return Response::json(['success' => true]);
    }


}
