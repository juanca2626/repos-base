<?php

namespace App\Http\Controllers;

use App\TrainTemplate;
use App\TrainChild;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainChildrenController extends Controller
{
    use Translations;

    public function index($train_template_id)
    {
        $children = TrainChild::where('train_template_id', $train_template_id)->get();

        $data = [
            'data' => $children,
            'success' => true
        ];

        return Response::json($data);
    }

    public function store($train_template_id, Request $request)
    {
        $train_template = TrainTemplate::find($train_template_id);
        $train_template->allow_child = 1;
        $train_template->save();

        $trainChild = new TrainChild();
        $trainChild->min_age = $request->input('child_min_age');
        $trainChild->max_age = $request->input('child_max_age');
        $trainChild->status = 1;
        $trainChild->train_template_id = $request->input('train_template_id');
        $trainChild->save();

        $response = ['success' => true, 'object_id' => $trainChild->id];

        return Response::json($response);
    }


    public function updateStatus($train_id, $child_id, Request $request)
    {
        $child = TrainChild::find($child_id);
        if ($request->input("status")) {
            $child->status = false;
        } else {
            $child->status = true;
        }
        $child->save();
        return Response::json(['success' => true]);
    }

    public function destroy($train_id, $child_id)
    {
        $child = TrainChild::find($child_id);
        $child->delete();

        return Response::json(['success' => true]);
    }

}
