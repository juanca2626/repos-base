<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\UseCases\Queries\SearchAllCity;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CityController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $cities = (new SearchAllCity())->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $cities);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
