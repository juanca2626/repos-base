<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileVipMapper;
use Src\Modules\File\Application\Mappers\VipMapper;
use Src\Modules\File\Application\UseCases\Commands\CreateFileVipCommand;
use Src\Modules\File\Application\UseCases\Commands\CreateVipCommand;
use Src\Modules\File\Application\UseCases\Queries\DestroyVipByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdFieldsQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchVipsQuery;
use Src\Modules\File\Application\UseCases\Queries\FindVipByIdQuery;
use Src\Modules\File\Presentation\Http\Requests\FileVip\CreateVipRequest;
use Src\Modules\File\Presentation\Http\Resources\VipResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class VipController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $params = VipMapper::fromRequestSearch($request);
            $file_vips = (new SearchVipsQuery($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_vips);
        } catch (\DomainException $domainException) {
            return $this->errorResponse(
                $domainException->getMessage(),
                ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    public function store(CreateVipRequest $request): JsonResponse
    {
        try {
            $vip_request = VipMapper::fromRequest($request);
            $vip = (new CreateVipCommand($vip_request))->execute();
            $request->merge(['vip_id' => $vip->id]);

            $file_vip_request = FileVipMapper::fromRequest($request, $request->input('file_id'));
            $file_vip = (new CreateFileVipCommand($file_vip_request))->execute();
            $response = true;

            if((int) @$request->__get('flag_lambda') == 1) {
                $file = (new FindFileByIdFieldsQuery(
                        $request->input('file_id'), ['file_number', 'description', 'lang'])
                    )->handle();
                $response = [
                    'file' => $file,
                    'vip' => $vip,
                    'file_vip' => $file_vip,
                ];
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show($id): VipResource
    {
        try {
            $vip = (new FindVipByIdQuery($id))->handle();
            return new VipResource($vip);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $response = (new DestroyVipByIdQuery($id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse(
                $domainException->getMessage(),
                ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
