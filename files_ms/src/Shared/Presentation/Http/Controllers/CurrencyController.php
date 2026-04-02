<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Shared\Application\UseCases\Queries\SearchAllCurrency;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CurrencyController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $currency = (new SearchAllCurrency())->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $currency);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
