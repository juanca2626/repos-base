<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;  
use Src\Modules\File\Application\UseCases\Commands\CreateFileCreditNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\CreateFileDebitNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileCreditNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileDebitNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileCreditNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileDebitNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementNewCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementForStellaQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileCreditNoteQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileDebitNoteQuery;
use Src\Modules\File\Presentation\Http\Resources\FileCreditNoteResource;
use Src\Modules\File\Presentation\Http\Resources\FileDebitNoteResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileDebitNoteController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $fileId)
    {
        try { 
            $results = (new SearchFileDebitNoteQuery($fileId,$request->input()))->handle(); 
            $results = FileDebitNoteResource::collection($results);
            return $this->successResponse(ResponseAlias::HTTP_OK, $results);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }   
        
    }
  

    public function store(Request $request, $file_id): JsonResponse
    {
        try { 
            
            (new CreateFileDebitNoteCommand($file_id, $request->input()))->execute();
            $response = (new FindFileStatementForStellaQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(Request $request, $file_id, $debit_note_id): JsonResponse
    {
        try { 
            
            (new UpdateFileDebitNoteCommand($file_id, $debit_note_id, $request->input()))->execute();
            $response = (new FindFileStatementForStellaQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(Request $request, $file_id, $debit_note_id): JsonResponse
    {
        try { 
            
            (new DeleteFileDebitNoteCommand($file_id, $debit_note_id))->execute();
            $response = (new FindFileStatementForStellaQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
}
