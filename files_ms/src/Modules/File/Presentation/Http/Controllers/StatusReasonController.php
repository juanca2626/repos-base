<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\Mappers\StatusReasonMapper;
use Src\Modules\File\Application\UseCases\Queries\SearchStatusReasonsQuery;
use Src\Modules\File\Presentation\Http\Resources\StatusReasonsResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StatusReasonController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $params = StatusReasonMapper::fromRequestSearch($request); 
            $file_status_reasons = (new SearchStatusReasonsQuery($params))->handle(); 
            $file_status_reasons = StatusReasonsResource::collection($file_status_reasons);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_status_reasons);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function reasons_for_cancellation(Request $request): JsonResponse
    {
        try {
            $params = [
                'status_iso' => ['XL']
            ];
            $file_status_reasons = (new SearchStatusReasonsQuery($params))->handle();  
            $file_status_reasons = StatusReasonsResource::collection($file_status_reasons);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_status_reasons);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

 
}
