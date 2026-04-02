<?php

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientBusinessRegionController extends Controller
{
    /**
     * Obtener las regiones de negocio asociadas a un cliente
     *
     * @param  int  $id (ID del cliente)
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Client $client)
    {
        try {
            $regions = $client->businessRegions()->get();

            return response()->json([
                'success' => true,
                'data' => $regions,
                'count' => $regions->count(),
                'message' => 'Regiones de negocio obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las regiones de negocio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asociar regiones de negocio a un cliente
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id (ID del cliente)
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Client $client)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'regions' => 'required|array',
                'regions.*' => 'exists:business_regions,id'
            ]);

            $client->syncBusinessRegionsWithRestore($request->regions);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Regiones de negocio actualizadas correctamente'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las regiones de negocio: ' . $e->getMessage()
            ], 500);
        }
    }
}
