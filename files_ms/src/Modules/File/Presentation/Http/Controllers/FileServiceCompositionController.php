<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileServiceCompositionMapper;
use Src\Modules\File\Application\UseCases\Commands\CreateFileServiceCompositionCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileServiceCompositionCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceCompositionConfirmationCodeCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceCompositionNotificationCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceCompositionScheduleCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileServiceByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchMasterServiceByIdQuery;
use Src\Modules\File\Presentation\Http\Resources\FileServiceCompositionResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileServiceCompositionController extends Controller
{
    use ApiResponse;


    public function store(Request $request, int $fileServiceId): FileServiceCompositionResource|JsonResponse
    {
        try {     

            $newFileServiceComposition = FileServiceCompositionMapper::fromRequestCreate($request->input(), $fileServiceId);   
            $fileServiceComposition = (new CreateFileServiceCompositionCommand($newFileServiceComposition))->execute();   
            
            // actualizamos el amount_cost servicio que es la suma de todos los compositions
            (new UpdateFileServiceTotalCostAmountCommand($fileServiceId))->execute();

            // recuperamos informacion del servicio para sacar file_itinerary_id 
            $fileService = (new FindFileServiceByIdQuery($fileServiceId))->handle(); 

            // recuperamos informacion del itinerario para sacar file_id 
            $fileItinerary = (new FindFileItineraryByIdQuery($fileService['file_itinerary_id']))->handle();         
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($fileService['file_itinerary_id']))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($fileService['file_itinerary_id']))->execute(); 

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary->fileId->value()))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $fileServiceComposition);
            // return new FileServiceCompositionResource($fileServiceComposition);             
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    } 

    public function destroy(Request $request, int $fileServiceId, int $fileServiceCompositionId): JsonResponse
    {
        try {            
            
            $response = (new DeleteFileServiceCompositionCommand($fileServiceCompositionId))->execute();   
            
            // recuperamos informacion del servicio para sacar file_itinerary_id 
            $fileService = (new FindFileServiceByIdQuery($fileServiceId))->handle(); 

            $fileItinerary = (new FindFileItineraryByIdQuery($fileService['file_itinerary_id']))->handle();         
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($fileService['file_itinerary_id']))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($fileService['file_itinerary_id']))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary->fileId->value()))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);           

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    } 

    public function update_schedule(int $id, Request $request): JsonResponse
    {
        try {
           
            $params = FileServiceCompositionMapper::fromRequestUpdateSchedule($request);
            $response = (new UpdateFileServiceCompositionScheduleCommand($id, $params))->execute();
            
            $response = [              
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$response['file_number']."/services/hours",
                'method' => 'put',
                'stela' => [
                    'master_services' =>$response['stela']
                ]               
            ];
            
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function confirmation_code(int $id, Request $request): JsonResponse
    {

        try
        {                       
 
            $response = (new UpdateFileServiceCompositionConfirmationCodeCommand($id, $request->input('code')))->execute(); 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/services/".$response['file_number']."/codcfm-service",
                'method' => 'post',
                'stella' => [
                    'services' => $response['services']
                ]
            ]);
            
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function update_notification(int $id, Request $request): JsonResponse
    {

        try
        {                       
 
            $response = (new UpdateFileServiceCompositionNotificationCommand($id))->execute(); 
            
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/services/".$response['file_number']."/codcfm-service",
                'method' => 'post',
                'stella' => [
                    'services' => $response['services']
                ]
            ]);
            
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
