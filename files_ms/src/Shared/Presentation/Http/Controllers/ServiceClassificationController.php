<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\UseCases\Queries\SearchAllServiceClassification;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Shared\Domain\Model\ClassificationService;
use Src\Shared\Application\UseCases\Commands\CreateClassificationService;

class ServiceClassificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $serviceClassification = (new SearchAllServiceClassification())->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $serviceClassification);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

      public function store(Request $request,CreateClassificationService $createClassificationService): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            $createdService = $createClassificationService->handle($validatedData);

            return $this->successResponse(ResponseAlias::HTTP_CREATED, $createdService);
        } catch (\DomainException $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            
                   return $this->errorResponse('An error occurred: ' . $exception->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
           // return $this->errorResponse('An error occurred while creating the type service.', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id, UpdateClassificationService $updateClassificationService): JsonResponse
{
    dd($id);
    // Validación de los datos recibidos
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    try {
        $updatedService = $updateClassificationService->handle($id, $validatedData);

        return $this->successResponse(ResponseAlias::HTTP_OK, $updatedService);
    } catch (\DomainException $domainException) {
        return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    } catch (\Exception $exception) {

        return $this->errorResponse('An error occurred: ' . $exception->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}
