<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Application\Mappers\FileHotelRoomMapper; 
use Src\Modules\File\Application\UseCases\Commands\CreateFileRoomAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\DeleteFileRoomAccommodationCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomUnitConfirmationCodeCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomUnitRqWlCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileHotelRoomUnitWlCodeCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery; 
use Src\Modules\File\Application\UseCases\Queries\FindFileHotelRoomUnitByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchMasterServiceByIdQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileHotelRoomUnitController extends Controller
{
    use ApiResponse;
 
    public function update_passengers(int $id, Request $request): JsonResponse
    {
        try {

            $fileHotelRoomUnit = (new FindFileHotelRoomUnitByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileHotelRoomUnit['file_hotel_room']['file_itinerary']['file_id']))->handle())['status']
            ));


            $passengers = FileHotelRoomMapper::fromRequestUpdatePassengers($request);
            $response = [];
                                  
            (new DeleteFileRoomAccommodationCommand($id))->execute();
            foreach($passengers as $passenger){
                (new CreateFileRoomAccommodationCommand($id, $passenger, ''))->execute();
            }

            if($request->__get('flag_lambda'))
            {
                $hotel_room_unit = (new SearchMasterServiceByIdQuery([
                    'file_hotel_room_unit_id' => $id, 'accommodations' => true, 'order' => true
                ]))->handle();

                $response = [
                    'type' => 'room',
                    'room' => $hotel_room_unit[0],
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
            $fileHotelRoomUnit = (new FindFileHotelRoomUnitByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileHotelRoomUnit['file_hotel_room']['file_itinerary']['file_id']))->handle())['status']
            ));


            $response = (new UpdateFileHotelRoomUnitConfirmationCodeCommand($id, $request->input('code')))->execute(); 
                             
 
            return $this->successResponse(ResponseAlias::HTTP_OK, [                
                'endpoint' => config('services.stella.endpoint') ."api/v1/commercial/ifx/services/".$fileHotelRoomUnit['file_hotel_room']['file_itinerary']['file']['file_number']."/codcfm-hotel",
                'method' => 'post',
                'stella' => $response
            ]);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function changes_rq_wl(int $id, Request $request): JsonResponse
    {

        try
        {                       
            $fileHotelRoomUnit = (new FindFileHotelRoomUnitByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileHotelRoomUnit['file_hotel_room']['file_itinerary']['file_id']))->handle())['status']
            ));


            $response = (new UpdateFileHotelRoomUnitRqWlCommand($id, $request->input()))->execute(); 
 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function changes_wl_code(int $id, Request $request): JsonResponse
    {

        try
        {                       
            $fileHotelRoomUnit = (new FindFileHotelRoomUnitByIdQuery($id))->handle();

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileHotelRoomUnit['file_hotel_room']['file_itinerary']['file_id']))->handle())['status']
            ));


            $response = (new UpdateFileHotelRoomUnitWlCodeCommand($id, $request->input('code')))->execute(); 
 
 
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
         

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
        

}
