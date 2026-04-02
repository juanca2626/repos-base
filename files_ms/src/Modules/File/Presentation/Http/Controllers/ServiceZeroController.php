<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Domain\Model\ServiceZero;
use Src\Modules\File\Application\UseCases\Commands\CreateServiceZeroCommand;
use Src\Modules\File\Application\UseCases\Commands\CreateOperationServiceZeroCommand;
use Src\Modules\File\Domain\Repositories\ServiceZeroRepositoryInterface; // Asegúrate de importar el repositorio correspondiente
use Src\Modules\File\Infrastructure\Persistence\Eloquent\ServiceZeroRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\OperationServiceZeroRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\DetailServiceZeroRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\RatesServiceZeroRepository;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ServiceZeroController extends Controller
{
    use ApiResponse;
    private $repository;

    public function __construct(ServiceZeroRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }



     public function index(): JsonResponse
    {
        try {
            // Obtener todos los servicios
            $services = $this->repository->all();

            return $this->successResponse(ResponseAlias::HTTP_OK, $services);
        } catch (\Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

       public function store(Request $request): JsonResponse
    {
        try {
         
        $serviceZero = $this->repository->save($request);
            return $this->successResponse(ResponseAlias::HTTP_CREATED, $serviceZero);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('An unexpected error occurred: ' . $e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

public function filter(Request $request)
{
    $params = $request->query();
    $serviceZeroRepository = new ServiceZeroRepository();
    $filteredServiceZero = $serviceZeroRepository->filter($params);
    return $this->successResponse(ResponseAlias::HTTP_OK, $filteredServiceZero);

    
}
}
