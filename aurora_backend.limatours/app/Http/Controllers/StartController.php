<?php

namespace App\Http\Controllers;

use App\Channel;
use App\RatesPlans;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class StartController extends Controller
{

    public function __construct()
    {
 
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function list()
    {
        
 
        return Response::json(['success' => true, 'data' => [
            ['id'=> 3, "description" => __('stars.descriptions', ['number' => 3])],
            ['id'=> 4, "description" => __('stars.descriptions', ['number' => 4])],
            ['id'=> 5, "description" => __('stars.descriptions', ['number' => 5])], 
        ]]);
    }

     

}
