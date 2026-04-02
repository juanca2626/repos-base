<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class SupplierController extends Controller
{
 
    private AuroraExternalApiService $auroraService;
    private ApiGatewayExternal $apiGatewayExternal;

    public function __construct(AuroraExternalApiService $auroraService, ApiGatewayExternal $apiGatewayExternal)
    {        
        $this->auroraService = $auroraService;
        $this->apiGatewayExternal = $apiGatewayExternal;
    }


    public function getSupplierData(string $code)
    {
        try {
            // Llamada al método getSuppliersByCode para obtener los datos del proveedor
            $suppliers = $this->apiGatewayExternal->getSuppliersByCode($code);

            // Si se encuentra el proveedor, devolver true
            if (!empty($suppliers)) {
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'data' => $suppliers,
                ], 200);
            }

            // Si no se encuentra el proveedor, devolver false (sin lanzar error)
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Supplier not found',
            ], 404);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Capturar específicamente el error 404 desde la API externa
            if ($e->getResponse()->getStatusCode() === 404) {
                // Si la API devuelve un 404, devolver false
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'Supplier not found',
                ], 404);
            }

            // Cualquier otro error del cliente HTTP
            return response()->json([
                'success' => false,
                'code' => $e->getResponse()->getStatusCode(),
                'message' => 'Client error occurred',
            ], $e->getResponse()->getStatusCode());

        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json([
                'success' => false,
                'code' => 404,
                'data' => [],
            ], 404);
        }
    }
  

}
