<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileVipMapper;
use Src\Modules\File\Application\UseCases\Commands\CreateFileVipCommand;
use Src\Modules\File\Application\UseCases\Queries\DestroyFileVipByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdFieldsQuery; 
use Src\Modules\File\Application\UseCases\Queries\FindFileVipByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileVipsQuery;
use Src\Modules\File\Application\UseCases\Queries\UpdateFileVipByIdQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Presentation\Http\Requests\FileVip\CreateFileVipRequest;
use Src\Modules\File\Presentation\Http\Resources\FileVip\FileVipResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileVipController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $params = FileVipMapper::fromRequestSearch($request);
            $file_vips = (new SearchFileVipsQuery($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_vips);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(CreateFileVipRequest $request, $file_id): JsonResponse
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($file_id))->handle())['status']               
            )); 
            
            $file_vip_request = FileVipMapper::fromRequest($request, $file_id);
            $file_vip = (new CreateFileVipCommand($file_vip_request))->execute();
            $response = true;

            if($request->__get('flag_lambda')) {
                $file = (new FindFileByIdFieldsQuery($file_id, ['file_number', 'description', 'lang']))->handle();

                $response = [
                    'file' => $file,
                    'file_vip' => $file_vip,
                ];
            }
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show($id): FileVipResource
    {
        try {
            $file_vip = (new FindFileVipByIdQuery($id))->handle();
            return new FileVipResource($file_vip);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(Request $request, $file_id , $id): JsonResponse
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($file_id))->handle())['status']               
            ));             

            $file_vip_request = FileVipMapper::fromRequest($request);
            $response = (new UpdateFileVipByIdQuery($id, $file_vip_request))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(Request $request, $file_id, $file_vip_id): JsonResponse
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($file_id))->handle())['status']               
            ));    
            
            $response = (new DestroyFileVipByIdQuery($file_id, $file_vip_id))->handle();

            if($request->__get('flag_lambda')) {
                $file = (new FindFileByIdFieldsQuery($file_id, ['file_number', 'description', 'lang']))->handle();
                $response = [
                    'file' => $file,
                ];
            }
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
