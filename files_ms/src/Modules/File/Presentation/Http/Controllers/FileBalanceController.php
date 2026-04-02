<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\File\Application\Exports\FileBalanceExport;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\SearchFileBalanceQuery;
// use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class FileBalanceController extends Controller{
    use ApiResponse;

    public function index(Request $request): JsonResponse {
        try {
            // $aurora = new AuroraExternalApiService();
            // $response = $aurora->quotesMarkup([299959,361874]);
            // return response()->json($response);

            $files = (new SearchFileBalanceQuery($request->input()))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $files);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function download(Request $request){
        try {
            $files = (new SearchFileBalanceQuery($request->input()))->handle();
            $files = $files->map(function ($item) {
                return $item;
            })->toArray();
            return Excel::download(new FileBalanceExport($files), 'file_balance.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
