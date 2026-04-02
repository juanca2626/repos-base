<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Presentation\Http\Resources\FileNoteExternalHousingResource;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdExternalHousingQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileNoteExternalHousingQuery;
use Src\Modules\File\Application\UseCases\Commands\CreateFileNoteExternalHousingCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileNoteExternalHousingCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileNoteExternalHousingCommand;
use Src\Modules\File\Presentation\Http\Requests\FileNoteExternalHousing\UpdateOrCreateNoteExternalHousingRequest;

class FileNoteExternalHousingController extends Controller{
    use ApiResponse;

    public function index(int $file_id): JsonResponse
    {
        try{
            $listExternalHousing = (new SearchFileNoteExternalHousingQuery($file_id))->handle();
            // AQUI HAY QUE CREAR UN RESOURCE PARA DEVOLVER LA RESPUESTA FORMATEADA
            if(!$listExternalHousing){
                return $this->successResponse(ResponseAlias::HTTP_OK,[]);
            }
            $listExternalHousing = FileNoteExternalHousingResource::collection($listExternalHousing);
            return $this->successResponse(ResponseAlias::HTTP_OK,$listExternalHousing);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(int $file_id,UpdateOrCreateNoteExternalHousingRequest $request): JsonResponse
    {
        try{
            $createExternalHousing = (new CreateFileNoteExternalHousingCommand($file_id, $request->input()))->execute();
            $externalHousing = new FileNoteExternalHousingResource($createExternalHousing);
            return $this->successResponse(ResponseAlias::HTTP_OK, $externalHousing);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $file_id, int $id): JsonResponse{
        try{
            $response = (new FindFileByIdExternalHousingQuery($file_id,$id))->handle();
            $externalHousing = new FileNoteExternalHousingResource($response);
            return $this->successResponse(ResponseAlias::HTTP_OK, $externalHousing);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(int $file_id, int $id, UpdateOrCreateNoteExternalHousingRequest $request): JsonResponse
    {
        try{
            $response = (new UpdateFileNoteExternalHousingCommand($file_id, $id, $request->input()))->execute();
            $externalHousing = new FileNoteExternalHousingResource($response);
            return $this->successResponse(ResponseAlias::HTTP_OK, $externalHousing);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $file_id, int $id): JsonResponse
    {
        try{
            $deleteExternalHousing = (new DeleteFileNoteExternalHousingCommand($file_id, $id))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, $deleteExternalHousing);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
