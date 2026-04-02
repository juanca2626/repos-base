<?php

namespace App\Http\Controllers;

use App\ServiceTypeRate;
use Illuminate\Support\Facades\Response;

class ServiceTypeRatesController extends Controller
{

    public function selectBox()
    {
        $ratesPlansTypes = ServiceTypeRate::select('id as value', 'name as text')
            ->get();


        return Response::json(['success' => true, 'data' => $ratesPlansTypes]);
    }
}
