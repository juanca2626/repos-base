<?php

namespace App\Http\Controllers;

use App\TrainTrainClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainTrainClassesController extends Controller
{

    public function getByTrain($train_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');

        $train_class = TrainTrainClass::where('train_id',$train_id);

        if ($querySearch) {
            $train_class->where('name', 'like', '%' . $querySearch . '%');
        }

        $count = $train_class->count();

        if ($paging === 1) {
            $train_class = $train_class->take($limit)->orderBy('created_at', 'desc')->get();
        } else {
            $train_class = $train_class->skip($limit * ($paging - 1))->orderBy('created_at', 'desc')
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
        $train_id = $request->input('train_id');
        $train_class_id = $request->input('train_class_id');
        $code = $request->input('code');

        $train_class = new TrainTrainClass;
        $train_class->name = $name;
        $train_class->train_id = $train_id;
        $train_class->train_class_id = $train_class_id;
        $train_class->code = $code;
        if( $train_class->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }


    public function update( $id, Request $request)
    {
        $name = $request->input('name');
        $train_id = $request->input('train_id');
        $train_class_id = $request->input('train_class_id');
        $code = $request->input('code');

        $train_class = TrainTrainClass::find($id);
        $train_class->name = $name;
        $train_class->train_id = $train_id;
        $train_class->train_class_id = $train_class_id;
        $train_class->code = $code;
        if( $train_class->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }


    public function destroy($id)
    {
        $train_class = TrainTrainClass::find($id);

        $train_class->delete();

        return Response::json(['success' => true]);

    }


}
