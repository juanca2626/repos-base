<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Presentation\Http\Resources\FileNoteGeneralResource;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteGeneralQuery;
use Src\Modules\File\Application\UseCases\Commands\CreateFileNoteGeneralCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileNoteGeneralCommand;
use Src\Modules\File\Presentation\Http\Requests\FileNoteGeneral\CreateFileNoteGeneralRequest;
use Src\Modules\File\Presentation\Http\Requests\FileNoteGeneral\UpdateFileNoteGeneralRequest;

class FileNoteGeneralController extends Controller {
    use ApiResponse;

    public function index(int $file_id): JsonResponse{
        try {

            $QueryfileNoteGeneral = (new SearchFileNoteGeneralQuery($file_id))->handle();
            if(!$QueryfileNoteGeneral){
                return $this->successResponse(ResponseAlias::HTTP_OK,[]);
            }
            $fileNoteGeneral = new FileNoteGeneralResource($QueryfileNoteGeneral);
            return $this->successResponse(ResponseAlias::HTTP_OK,$fileNoteGeneral);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function create(int $file_id, CreateFileNoteGeneralRequest $request) : JsonResponse{
        try {
            $fileNoteGeneralCommand = (new CreateFileNoteGeneralCommand($file_id, $request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK,$fileNoteGeneralCommand);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $file_id, int $note_id, UpdateFileNoteGeneralRequest $request) : JsonResponse {
        try {
            $fileNoteGeneralCommand = (new UpdateFileNoteGeneralCommand($note_id, $file_id, $request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK,$fileNoteGeneralCommand);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
