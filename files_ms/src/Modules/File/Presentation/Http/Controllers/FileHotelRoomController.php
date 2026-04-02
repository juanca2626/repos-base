<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileHotelRoomMapper;
use Src\Modules\File\Application\UseCases\Commands\CreateFileRoomAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileRoomAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomAmountCostCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomConfirmationCodeCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand; 
use Src\Modules\File\Application\UseCases\Queries\FindFileHotelRoomByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileAmountTypeFlagLockedQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchMasterServiceByIdQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\File\Application\Exports\FileRoomExport;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;

class FileHotelRoomController extends Controller
{
    use ApiResponse;


    public function index(int $id, Request $request): JsonResponse
{
    
    try {
        $fileRoom = (new FindFileHotelRoomByIdQuery($id))->handle();

        $fileStatus = (new FindFileByIdAllQuery($fileRoom['file_itinerary']['file_id']))->handle()['status'];
        (new FileValidateStatus($fileStatus));

        if ($request->__get('flag_lambda')) {
            $master_services = (new SearchMasterServiceByIdQuery([
                'file_hotel_room_id' => $id,
                'order' => true
            ]))->handle();

            $response = [
                'type' => 'room',
                'room' => $master_services[0],
                'file_room' => $fileRoom,
            ];
        } else {
            $response = [
                'file_room' => $fileRoom,
            ];
        }

        return $this->successResponse(ResponseAlias::HTTP_OK, $response);

    } catch (\DomainException $domainException) {
        // Manejar errores de dominio con un mensaje de error
        return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }
}
 
    public function update_amount(int $id, Request $request): JsonResponse
    {
        try {
            $params = FileHotelRoomMapper::fromRequestUpdateAmountCost($request);

            if($params['file_amount_type_flag_id']->value() === 0) {
                $fileAmountTypeFlags = (new SearchFileAmountTypeFlagLockedQuery())->handle();
                $params['file_amount_type_flag_id']->setValue($fileAmountTypeFlags['id']);
            }
                               
            $fileRoom = (new FindFileHotelRoomByIdQuery($id))->handle();
            
            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileRoom['file_itinerary']['file_id']))->handle())['status']
            )); 

            $response = (new UpdateFileHotelRoomAmountCostCommand($id, $params))->execute(); 

            // actualizamos el total_cost_amount itinerario
            (new UpdateFileItineraryTotalCostAmountCommand($fileRoom['file_itinerary']['id']))->execute();

            // actualizamos el profitability itinerario
            (new UpdateFileItineraryProfitabilityCommand($fileRoom['file_itinerary']['id']))->execute();

            // actualizamos el file statement
            (new UpdateFileStatementCommand($fileRoom['file_itinerary']['file']['id']))->execute();
              
            if ($request->__get('flag_lambda')) {
                return $this->successResponse(ResponseAlias::HTTP_OK, $response);
            }else{
                return $this->successResponse(ResponseAlias::HTTP_OK, true);
            }
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update_passengers(int $id, Request $request): JsonResponse
    {
        try {
            $passengers = FileHotelRoomMapper::fromRequestUpdatePassengers($request);
            $fileHotelRoom = (new FindFileHotelRoomByIdQuery($id))->handle();
            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileHotelRoom['file_itinerary']['file_id']))->handle())['status']
            ));
            
            
            $response = [];   
            $correlative = 0;                 
            foreach($fileHotelRoom['hotel_room_units'] as $index => $unit) {
                (new DeleteFileRoomAccommodationCommand($unit['id']))->execute();
                $pax = $unit['adult_num'] + $unit['child_num'] + $unit['infant_num'];
                $passenger_slices = array_slice($passengers, $correlative, $pax);
                $correlative = $correlative + $pax;
                foreach($passenger_slices as $passenger) {
                    (new CreateFileRoomAccommodationCommand($unit['id'], $passenger, ''))->execute();
                }
            }

            if($request->__get('flag_lambda'))
            {
                $hotel_room = (new SearchMasterServiceByIdQuery([
                    'file_hotel_room_id' => $id, 'accommodations' => true, 'order' => true
                ]))->handle();

                $response = [
                    'type' => 'room',
                    'room' => $hotel_room,
                ];
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
    public function confirmation_code(int $id, Request $request): JsonResponse
    {

        try
        {                       
            $fileRoom = (new FindFileHotelRoomByIdQuery($id))->handle();
                
            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileRoom['file_itinerary']['file_id']))->handle())['status']
            )); 
                    
            $response = (new UpdateFileHotelRoomConfirmationCodeCommand($id, $request->input('code')))->execute(); 
 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/services/".$fileRoom['file_itinerary']['file']['file_number']."/codcfm-hotel",
                'method' => 'post',
                'stella' => $response
            ]);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    

    public function cancel(): JsonResponse
    {
        try {
            $response = [];
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            
        }
    }    
    
     public function exportToExcel(int $id)
     
    {
        try {
            $fileRoom = (new FindFileHotelRoomByIdQuery($id))->handle();
            $fileStatus = (new FindFileByIdAllQuery($fileRoom['file_itinerary']['file_id']))->handle()['status'];
            (new FileValidateStatus($fileStatus));

            $export = new FileRoomExport($fileRoom);

            return Excel::download($export, 'file_room_export.xlsx');

        } catch (\DomainException $domainException) {
            // Si ocurre un error, puedes devolver un error 422
            return response()->json(['error' => $domainException->getMessage()], 422);
        }
    }
}
