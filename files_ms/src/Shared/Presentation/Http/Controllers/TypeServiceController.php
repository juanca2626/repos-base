<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\UseCases\Queries\SearchAllTypeService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class TypeServiceController extends Controller
{

    public function __construct(AuroraExternalApiService $auroraService)
    {
        $this->auroraService = $auroraService;
    }

public function index(Request $request)
{
    try {
        $token = str_replace('Bearer ', '', $request->input('Authorization'));

        $response = $this->auroraService->getServiceTypes('es', $token);

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

   

     public function store(Request $request): JsonResponse
    {
        // Validación de los datos recibidos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            // Crear el servicio de tipo con los datos validados
            $typeService = (new CreateTypeService())->handle($validatedData);
            // Responder con éxito si la creación fue exitosa
            return $this->successResponse(ResponseAlias::HTTP_CREATED, $typeService);
        } catch (\DomainException $domainException) {
            // Manejo de excepciones de dominio
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            // Manejo de cualquier otra excepción
            return $this->errorResponse('An error occurred while creating the type service.', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
