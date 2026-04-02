<?php

namespace App\Http\Controllers;

use App\Quote;
use App\QuoteDynamicPrice;
use Illuminate\Http\Request;

class QuoteDynamicPriceController extends Controller
{
    public function store(Request $request)
    {
        // Validar los campos de la solicitud si es necesario
        $request->validate([
            'quote_id' => 'required',
            'quote_service_id' => 'required',
            'object_id' => 'required',
            'type' => 'required',
            'price_adl' => 'required|numeric|min:0',
            'markup' => 'required|numeric|min:0',
        ]);

        // Asignar los valores de los campos desde la solicitud

        $data = QuoteDynamicPrice::updateOrCreate([
            'quote_id' => $request->quote_id,
            'quote_service_id' => $request->quote_service_id,
            'type' => $request->type,
            'object_id' => $request->object_id,
            'client_id' => $request->client_id,
        ], $request->all());

        // Retornar una respuesta adecuada
        return response()->json([
            'success' => true,
            'message' => 'Creado correctamente',
            'data' => $data
        ], 201);
    }
}
