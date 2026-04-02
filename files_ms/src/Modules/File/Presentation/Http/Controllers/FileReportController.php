<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;  
use Src\Modules\File\Application\UseCases\Commands\CreateFilePassengerModifyPaxCommand; 
use Src\Modules\File\Application\UseCases\Queries\SearchFilePassengerModifyPaxQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileReport;
use Src\Modules\File\Application\UseCases\Queries\SearchFileReportByHotel;
use Src\Modules\File\Presentation\Http\Resources\FilePassengerModifyPaxResource; 
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $fileId): JsonResponse
    {
         
        try {

            (new CreateFilePassengerModifyPaxCommand($fileId))->execute();
               
            $file_passenger_modify_paxs = (new SearchFilePassengerModifyPaxQuery($fileId))->handle(); 
            $file_passenger_modify_paxs = FilePassengerModifyPaxResource::collection($file_passenger_modify_paxs);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_passenger_modify_paxs);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
 
    public function list(Request $request, int $fileId): JsonResponse
    {
        try {
      
            $itineraries = (new SearchFileReport($fileId, $request->input()))->handle();
                
            return $this->successResponse(ResponseAlias::HTTP_OK, $itineraries);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function search_hotel(Request $request, int $fileId, string $hotel_code): JsonResponse
    {
        try {
      
            $itineraries = (new SearchFileReportByHotel($fileId, $hotel_code ,$request->input()))->handle();
                
            return $this->successResponse(ResponseAlias::HTTP_OK, $itineraries);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    

}
