<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 
use Src\Modules\File\Application\UseCases\Queries\SearchFileReasonStatementQuery; 
use Src\Modules\File\Presentation\Http\Resources\FileReasonStatementResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileReasonStatementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try { 
            $file_reasons = (new SearchFileReasonStatementQuery($request->input()))->handle();
            $file_reasons = FileReasonStatementResource::collection($file_reasons);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_reasons);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
 
}
