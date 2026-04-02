<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteForFileOpeQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteForServiceOpeQuery;

class FileNoteOpeController extends Controller{
    use ApiResponse;

    public function index(int $file_number): JsonResponse{
        try{
            $fileNoteForFileOpe = (new SearchFileNoteForFileOpeQuery($file_number))->handle();
            $fileNoteForServiceOpe = (new SearchFileNoteForServiceOpeQuery($file_number))->handle();

            return $this->successResponse(ResponseAlias::HTTP_OK,[
                "for_file"      => $fileNoteForFileOpe,
                "for_service"   => $fileNoteForServiceOpe,
            ]);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
