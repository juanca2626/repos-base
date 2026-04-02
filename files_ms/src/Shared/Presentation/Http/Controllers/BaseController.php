<?php

namespace Src\Shared\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Src\Shared\Presentation\Http\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    use ApiResponse;

    protected function ok($data = null): JsonResponse
    {
        return $this->successResponse(ResponseAlias::HTTP_OK, $data);
    }

    protected function created($data = null): JsonResponse
    {
        return $this->successResponse(ResponseAlias::HTTP_CREATED, $data);
    }
}