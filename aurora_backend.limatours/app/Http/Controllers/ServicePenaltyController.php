<?php

namespace App\Http\Controllers;

use App\ServicePenalty;
use Illuminate\Support\Facades\Response;

class ServicePenaltyController extends Controller
{

    public function selectBox()
    {
        $penalties = ServicePenalty::select('id', 'name')->where('status', 1)->get();
        $result = [];
        foreach ($penalties as $penalty) {
            array_push($result, ['text' => $penalty->name, 'value' => $penalty->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }
}
