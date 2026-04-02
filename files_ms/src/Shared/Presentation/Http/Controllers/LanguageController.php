<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\UseCases\Queries\SearchAllLanguages;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LanguageController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $languages = (new SearchAllLanguages())->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $languages);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
