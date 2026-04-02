<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\UseCases\Queries\SearchCategoryQuery;
use Src\Modules\File\Presentation\Http\Resources\CategoriesResource; 
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try { 
            $file_categories = (new SearchCategoryQuery($request->input()))->handle();
            $file_categories = CategoriesResource::collection($file_categories);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_categories);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
 
}
