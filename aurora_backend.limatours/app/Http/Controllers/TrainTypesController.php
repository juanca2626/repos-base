<?php

namespace App\Http\Controllers;

use App\TrainType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainTypesController extends Controller
{
    public function index(){
        $data = TrainType::all();
        return Response::json($data);
    }
}
