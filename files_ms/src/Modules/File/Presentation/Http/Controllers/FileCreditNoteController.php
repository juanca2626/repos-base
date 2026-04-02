<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;  
use Src\Modules\File\Application\UseCases\Commands\CreateFileCreditNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileCreditNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileCreditNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementNewCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementForStellaQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileCreditNoteQuery;
use Src\Modules\File\Presentation\Http\Resources\FileCreditNoteResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileCreditNoteController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $fileId)
    {
        try { 
            $results = (new SearchFileCreditNoteQuery($fileId,$request->input()))->handle(); 
            $results = FileCreditNoteResource::collection($results);
            return $this->successResponse(ResponseAlias::HTTP_OK, $results);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }   
        
    }
  

    public function store(Request $request, $file_id): JsonResponse
    {
        try { 
            
            (new CreateFileCreditNoteCommand($file_id, $request->input()))->execute();
            $response = (new FindFileStatementForStellaQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(Request $request, $file_id, $credit_note_id): JsonResponse
    {
        try { 
            
            (new UpdateFileCreditNoteCommand($file_id, $credit_note_id, $request->input()))->execute();
            $response = (new FindFileStatementForStellaQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(Request $request, $file_id, $credit_note_id): JsonResponse
    {
        try { 
            
            (new DeleteFileCreditNoteCommand($file_id, $credit_note_id))->execute();
            $response = (new FindFileStatementForStellaQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    

}
