<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\Mappers\FileAmountReasonMapper;
use Src\Modules\File\Application\UseCases\Queries\SearchFileAmountReasonsQuery;
use Src\Modules\File\Presentation\Http\Resources\FileAmountReasonsResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileAmountReasonController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $params = FileAmountReasonMapper::fromRequestSearch($request);
            $file_amount_reasons = (new SearchFileAmountReasonsQuery($params))->handle();
            $file_amount_reasons = FileAmountReasonsResource::collection($file_amount_reasons);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_amount_reasons);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
 
}
