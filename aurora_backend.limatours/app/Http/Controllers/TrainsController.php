<?php

namespace App\Http\Controllers;

use App\Train;
use App\TrainRailRoute;
use App\TrainTrainClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainsController extends Controller
{
    public function index()
    {
        $trains = Train::all();

        return Response::json(['success' => true, 'data' => $trains]);
    }

    public function routes($train_id)
    {
        $train_routes = TrainRailRoute::where('train_id', $train_id)->get();

        return Response::json(['success' => true, 'data' => $train_routes]);
    }

    public function classes($train_id)
    {
        $train_classes = TrainTrainClass::where('train_id', $train_id)->get();

        return Response::json(['success' => true, 'data' => $train_classes]);
    }
}
