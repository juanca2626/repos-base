<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileServiceMapper;
use Src\Modules\File\Application\UseCases\Commands\CreateFileServiceCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileServiceCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceScheduleCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceAmountCostCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand; 
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileServiceByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileAmountTypeFlagLockedQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchMasterServiceByIdQuery;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Presentation\Http\Resources\FileServiceResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\File\Application\Exports\ServicesScheduleExport;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileServiceDateCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;

class FileServiceController extends Controller
{
    use ApiResponse;


    public function index(int $id, Request $request): JsonResponse
{
    try {
        // Obtener los servicios maestros para el archivo de servicio con ID especificado
        $master_services = (new SearchMasterServiceByIdQuery(['file_service_id' => $id]))->handle();

        // Si se usa un flag (como 'flag_lambda'), podríamos incluir una respuesta adicional.
        if ($request->__get('flag_lambda')) {
            $master_services_prev = (
                new SearchMasterServiceByIdQuery(['file_service_id' => $id, 'order' => true])
            )->handle();

            $response = [
                'master_services_prev' => $master_services_prev,
                'master_services' => $master_services,
            ];
        } else {
            $response = [
                'master_services' => $master_services,
            ];
        }
        return $this->successResponse(ResponseAlias::HTTP_OK, $response);

    } catch (\DomainException $domainException) {
        return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }
}


    public function store(Request $request, int $fileItineraryId): FileServiceResource|JsonResponse
    {
        try {      
            $newFileService = FileServiceMapper::fromRequestCreate($request, $fileItineraryId);    
            $fileService = (new CreateFileServiceCommand($newFileService))->execute();   
            
            $fileItinerary = (new FindFileItineraryByIdQuery($fileItineraryId))->handle();         
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($fileItineraryId))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($fileItineraryId))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileItinerary->fileId->value()))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $fileService);

            // return new FileServiceResource($fileService);             
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    } 

    public function destroy(Request $request, int $fileItineraryId, int $fileServiceId): FileServiceResource|JsonResponse
    {
        try {            
 
            $response = (new DeleteFileServiceCommand($fileServiceId))->execute();   
            
            $fileItinerary = (new FindFileItineraryByIdQuery($fileItineraryId))->handle();         
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($fileItineraryId))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($fileItineraryId))->execute();

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
                         
            $params = FileServiceMapper::fromRequestUpdateSchedule($request);
            $response = (new UpdateFileServiceScheduleCommand($id, $params))->execute();
            //$master_services = (new SearchMasterServiceByIdQuery(['file_service_id' => $id]))->handle();

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

    public function update_date(int $id, Request $request): JsonResponse
    {
        try {
                         
            $new_date = $request->input('date');
            $response = (new UpdateFileServiceDateCommand($id, $new_date))->execute();

            $response = [              
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/files/".$response['file_number']."/services/date",
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

    public function update_amount(int $id, Request $request): JsonResponse
    {
        try {
     
    
            $params = FileServiceMapper::fromRequestUpdateAmountCost($request);
     
            if($params['file_amount_type_flag_id']->value() === 0)
            {
                $fileAmountTypeFlags = (new SearchFileAmountTypeFlagLockedQuery())->handle();
                $params['file_amount_type_flag_id']->setValue($fileAmountTypeFlags['id']);
            }
            $response = (new UpdateFileServiceAmountCostCommand($id, $params))->execute();
            $fileService = (new FindFileServiceByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileService['file_itinerary']['file_id']))->handle())['status']
            )); 
 
            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($fileService['file_itinerary']['id']))->execute();
            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($fileService['file_itinerary']['id']))->execute();
            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileService['file_itinerary']['file']['id']))->execute();
            
            if ($request->__get('flag_lambda')) {
                return $this->successResponse(ResponseAlias::HTTP_OK, $response);
            }else{
                return $this->successResponse(ResponseAlias::HTTP_OK, true);
            }
        
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


      public function exportToExcel(Request $request,int $id)
    {
        try {
            // Obtener los servicios maestros para el archivo de servicio con ID especificado
            $master_services = (new SearchMasterServiceByIdQuery(['file_service_id' => $id]))->handle();

            // Exportar los datos a Excel
            return Excel::download(new ServicesScheduleExport($master_services), 'services_schedule.xlsx');

        } catch (\DomainException $domainException) {
            // Si hay un error, puedes manejarlo adecuadamente
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
 
}
