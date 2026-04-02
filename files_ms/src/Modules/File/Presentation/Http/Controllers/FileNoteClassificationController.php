<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Presentation\Http\Resources\FileNoteClassificationResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileNoteClassificationController extends Controller{

    use ApiResponse;

    public function index(): JsonResponse{
        try {
            $stella = new ApiGatewayExternal();
            $masterServices = (array) $stella->getNoteClassification();

            $masterServicesArray = json_decode(json_encode($masterServices), true);

            $masterServices = FileNoteClassificationResource::collection($masterServicesArray);
            return $this->successResponse(ResponseAlias::HTTP_OK,$masterServices);
            // return response()->json(['data'=>$masterServices],200);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
