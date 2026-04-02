<?php

namespace App\Http\Controllers;

use App\RatesPlansTypes;
use Illuminate\Support\Facades\Response;

class RatesPlansTypesController extends Controller
{

    public function selectBox()
    {
        $ratesPlansTypes = RatesPlansTypes::select('id as value', 'name as text')
            ->get();


        return Response::json(['success' => true, 'data' => $ratesPlansTypes]);
    }
}
