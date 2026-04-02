<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 
use Src\Modules\File\Application\UseCases\Queries\SearchSupplierQuery; 
use Src\Modules\File\Presentation\Http\Resources\SuppliersResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SupplierController extends Controller
{
    use ApiResponse;

    public function __construct(AuroraExternalApiService $auroraService)
    {
        $this->auroraService = $auroraService;
    }

    public function index(Request $request): JsonResponse
    {
        try { 
            $file_categories = (new SearchSupplierQuery($request->input()))->handle();
            $file_categories = SuppliersResource::collection($file_categories);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_categories);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    
 
}
