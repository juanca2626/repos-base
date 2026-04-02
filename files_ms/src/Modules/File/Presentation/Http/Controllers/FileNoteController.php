<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Src\Modules\File\Presentation\Http\Resources\FileNoteResource;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteQuery;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Commands\CreateFileNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileNoteCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileNoteCommand;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteAllQuery;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileNoteStatusCommand;
use Src\Modules\File\Presentation\Http\Requests\FileNote\CreateFileNoteRequest;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteAllRequirementQuery;
use Src\Modules\File\Presentation\Http\Requests\FileNoteStatus\UpdateFileNoteStatusRequest;
// use Src\Modules\File\Presentation\Http\Requests\FileNote\UpdateFileNoteRequest;

// use Src\Modules\File\Presentation\Http\Resources\FileNoteResource;

class FileNoteController extends Controller{
    use ApiResponse;

    public function list_note_all(int $file_id): JsonResponse {
        try{
            $listNoteAll = (new SearchFileNoteAllQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $listNoteAll);
            return response()->json(["TODO"]);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function list_note_all_requirement(int $file_id): JsonResponse {
        try{
            $listNoteAll = (new SearchFileNoteAllRequirementQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $listNoteAll);
            return response()->json(["TODO"]);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update_note_status_ope(int $id, UpdateFileNoteStatusRequest $request): JsonResponse{
        try{
            $approved_note = (new UpdateFileNoteStatusCommand($id,$request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $approved_note);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function index($file_id): JsonResponse{
        try{
            $listNote = (new SearchFileNoteQuery($file_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $listNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(CreateFileNoteRequest $request, $file_id): JsonResponse
    {
        try{
            $createNote = (new CreateFileNoteCommand($file_id,$request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $createNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(CreateFileNoteRequest $request, int $file_id, int $note): JsonResponse
    {
        try{
            $updateNote = (new UpdateFileNoteCommand($file_id, $note,$request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $updateNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy($file_id, $note_id, Request $request){
        try{
            $deleteNote = (new DeleteFileNoteCommand($file_id, $note_id, $request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $deleteNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
