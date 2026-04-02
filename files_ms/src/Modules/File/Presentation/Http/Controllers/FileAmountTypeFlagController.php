<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\Mappers\FileAmountTypeFlagMapper;
use Src\Modules\File\Application\UseCases\Queries\SearchFileAmountTypeFlagsQuery;
use Src\Modules\File\Presentation\Http\Resources\FileAmountTypeFlagResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileAmountTypeFlagController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $params = FileAmountTypeFlagMapper::fromRequestSearch($request);
            $file_amount_type_flags = (new SearchFileAmountTypeFlagsQuery($params))->handle();
            $file_amount_type_flags = FileAmountTypeFlagResource::collection($file_amount_type_flags);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_amount_type_flags);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
