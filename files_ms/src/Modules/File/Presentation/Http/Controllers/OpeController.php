<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse; 
use Illuminate\Http\Request; 
use Src\Modules\File\Application\UseCases\Queries\SearchFilesPassToOpe;
use Src\Modules\File\Application\UseCases\Queries\SearchHistoryPassToOpe;
use Src\Modules\File\Presentation\Http\Resources\CategoriesResource;
use Src\Modules\File\Presentation\Http\Resources\FileHistoryPassToOpeResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OpeController extends Controller
{
    use ApiResponse;

    public function passToOpe(Request $request)
    {
        try {

            $searchFilesPassToOpe = (
                new SearchFilesPassToOpe($request->input())
            )->handle();

            return $this->successResponse(ResponseAlias::HTTP_OK, $searchFilesPassToOpe);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function historyPassToOpe(Request $request)
    {
        try {

            $searchHistoryPassToOpe = (
                new SearchHistoryPassToOpe($request->input())
            )->handle();
            
            return response()->json([
                'success' => true,
                'data' => FileHistoryPassToOpeResource::collection($searchHistoryPassToOpe->items()) ,
                'pagination' => [
                    'current_page' => $searchHistoryPassToOpe->currentPage(),
                    'last_page' => $searchHistoryPassToOpe->lastPage(),
                    'per_page' => $searchHistoryPassToOpe->perPage(),
                    'total' => $searchHistoryPassToOpe->total(),
                ],
                'code' => ResponseAlias::HTTP_OK
            ], ResponseAlias::HTTP_OK);           

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }
 
}
