<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MasterServiceTestStella extends Controller
{

    public function response_test(Request $request)
    {
        $response = [
            'success' => true,
            'message' => "Prueba de respuesta satisfactoria",
        ];
        return Response::json($response);

    }
}
