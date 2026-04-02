<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Src\Modules\File\Presentation\Http\Resources\FileNoteResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteItineraryQuery;
use Src\Modules\File\Application\UseCases\Commands\CreateFileNoteItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileNoteItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileNoteItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileNoteItineraryServiceCommand;
use Src\Modules\File\Presentation\Http\Requests\FileNoteItinerary\CreateFileNoteItineraryRequest;
use Src\Modules\File\Presentation\Http\Requests\FileNoteItinerary\UpdateFileNoteItineraryRequest;

// use Src\Modules\File\Presentation\Http\Resources\FileNoteResource;

class FileNoteItineraryController extends Controller{
    use ApiResponse;

    public function list_note_itinerary($file_id, $itinerary_id): JsonResponse|FileNoteResource{

        // return response()->json(['file'=>'id','id'=>$id]);
        try{
            $fileNoteItinerary = (new SearchFileNoteItineraryQuery($file_id, $itinerary_id))->handle();
            $arrValores = FileNoteResource::collection($fileNoteItinerary);
            return $this->successResponse(ResponseAlias::HTTP_OK,$arrValores);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function create_note_itinerary(CreateFileNoteItineraryRequest $request,$file_id, $itinerary_id): JsonResponse
    {
        try{
            $createNote = (new CreateFileNoteItineraryCommand($file_id,$itinerary_id,$request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $createNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $this->successResponse(ResponseAlias::HTTP_OK, true);
    }

    public function update_note_itinerary(UpdateFileNoteItineraryRequest $request, $file_id, $itinerary_id, $note_id): JsonResponse
    {
        try{
            $updateNote = (new UpdateFileNoteItineraryCommand($file_id, $itinerary_id, $note_id, $request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $updateNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function delete_note_itinerary($file_id, $note_id, Request $request): JsonResponse
    {
        try{
            $deleteNote = (new DeleteFileNoteItineraryCommand($file_id,$note_id,$request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $deleteNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function delete_note_itinerary_service($itinerary_id, Request $request): JsonResponse{
        try{
            $deleteNote = (new DeleteFileNoteItineraryServiceCommand($itinerary_id,$request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $deleteNote);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
