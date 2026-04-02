<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceUnitConfirmationCodeCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceUnitRqWlCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceUnitWlCodeCommand;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileServiceUnitController extends Controller
{
    use ApiResponse;

 
    public function confirmation_code(int $id, Request $request): JsonResponse
    {

        try
        {     
            $response = (new UpdateFileServiceUnitConfirmationCodeCommand($id, $request->input('code')))->execute(); 
 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function changes_rq_wl(int $id, Request $request): JsonResponse
    {

        try
        {                       
 
            $response = (new UpdateFileServiceUnitRqWlCommand($id, $request->input()))->execute(); 
 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function changes_wl_code(int $id, Request $request): JsonResponse
    {

        try
        {                             
            $response = (new UpdateFileServiceUnitWlCodeCommand($id, $request->input('code')))->execute(); 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
