<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Carbon\Carbon; 
use Src\Modules\File\Domain\Repositories\FileHotelRoomUnitRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomUnitEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;

class FileHotelRoomUnitRepository implements FileHotelRoomUnitRepositoryInterface
{

    public function updateStatus(int $id, int $status): bool
    {  
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->status = $status;
        $fileHotelRoomUnitEloquent->save();
        
        return true;
    }

    public function findById(int $id): array
    {       
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->with([
            'fileHotelRoom.fileItinerary.file'           
        ])->findOrFail($id);
 
        return $fileHotelRoomUnitEloquent->toArray();
    }

    public function updateAmountCost(int $id, float $newAmountCost): bool
    {  
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->amount_cost = $newAmountCost;
        $fileHotelRoomUnitEloquent->save();

        return true;
    }

    public function updateAmountSale(int $id, float $newAmountCost): bool
    {  
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->amount_sale = $newAmountCost;
        $fileHotelRoomUnitEloquent->save();

        return true;
    }

    public function updateConfirmationCode(int $id, string $code): array
    {  
        
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->with([ 
            'fileHotelRoom.fileItinerary'           
        ])->find($id);

        $fileHotelRoomUnitEloquent->confirmation_status = true;        
        $fileHotelRoomUnitEloquent->waiting_list = false;
        $fileHotelRoomUnitEloquent->confirmation_code = $code;
        $fileHotelRoomUnitEloquent->save();
        
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([ 
            'fileItinerary','hotelRoomUnits'           
        ])->find($fileHotelRoomUnitEloquent->file_hotel_room_id);

        $stella = [];

        if(!$fileHotelRoomEloquent->confirmation_code or $fileHotelRoomEloquent->confirmation_code == '' or $fileHotelRoomEloquent->confirmation_code == "0") // solo si no tiene asociado confirmation_code procesamos a validar si enviamos actualizar stella 
        {
            $assing_all_confirmation_code = true; //validamos que todos los units del room su estado este confirmado
            foreach($fileHotelRoomEloquent->hotelRoomUnits as $hotelRoomUnits)
            {              
                if(!$hotelRoomUnits->confirmation_code)
                {                    
                    $assing_all_confirmation_code = false;
                }                
            }
    
            if($assing_all_confirmation_code == true)
            {
               $fileHotelRoomEloquent->confirmation_status = true;        
               $fileHotelRoomEloquent->waiting_list = false;
               $fileHotelRoomEloquent->confirmation_code = $code;
               $fileHotelRoomEloquent->save();


               $auto_order = NULL;        
               foreach($fileHotelRoomEloquent->fileItinerary->rooms as $index => $rooms)
               {
                   if($rooms->id == $fileHotelRoomEloquent->id)
                   {
                       $auto_order = ($index + 1);
                   }    
               }
       
               if($auto_order == NULL){
                   throw new \DomainException('auto_order does not exist');
               }

               
               $stella = [
                    "services" => [
                        [                             
                            "code" => $fileHotelRoomEloquent->fileItinerary->object_code,
                            "date_in" => Carbon::parse($fileHotelRoomEloquent->fileItinerary->date_in)->format('d/m/Y'),
                            "auto_order" => $auto_order,
                            "confirmation_code" => $code
                                                        
                        ]
                    ]
                ];
            }
        }


        // actualizaremos el itineario si es que todos los hotel rooms estan confirmados 
        $fileItineryEloquentModel = FileItineraryEloquentModel::query()->with(['rooms'])->find($fileHotelRoomEloquent->file_itinerary_id);
         
        $assing_all_confirmation_code = true;  
        foreach($fileItineryEloquentModel->rooms as $room)
        {
            if($room->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileItineryEloquentModel->confirmation_status = true;    
            $fileItineryEloquentModel->save();
        } 



        return $stella;

    }

    public function updateRqWl(int $id, array $params): bool
    {  
        
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->with([ 
            'fileHotelRoom.fileItinerary'           
        ])->find($id);

        if($fileHotelRoomUnitEloquent->confirmation_status != 0){
            throw new \DomainException("the record is not RQ");
        }

        $fileHotelRoomUnitEloquent->waiting_list = true;
        $fileHotelRoomUnitEloquent->waiting_reason_id = $params['waiting_reason_id'];        
        $fileHotelRoomUnitEloquent->waiting_reason_other_message = $params['waiting_reason_other_message'];        
        $fileHotelRoomUnitEloquent->waiting_confirmation_code = $params['waiting_confirmation_code']; 
        $fileHotelRoomUnitEloquent->save();
        
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([ 
            'hotelRoomUnits',            
        ])->find($fileHotelRoomUnitEloquent->file_hotel_room_id);

        
        $status_all = [
            'ok' => 9,
            'rq' => 9,
            'wl' => 9
        ];  

        foreach($fileHotelRoomEloquent->hotelRoomUnits as $hotelRoomUnits)
        {
            if($hotelRoomUnits->confirmation_status == true)
            {
                $status_all['ok'] = 3;

            }else{
                if($hotelRoomUnits->waiting_list == true)
                {
                    $status_all['wl'] = 2;
                }else
                {
                    $status_all['rq'] = 1;
                }
            }
        }

        asort($status_all);
        $status_new = array_key_first($status_all);

        if($status_new == "wl" and $fileHotelRoomEloquent->confirmation_status == 0)
        {                
            $fileHotelRoomEloquent->waiting_list = true;
            $fileHotelRoomEloquent->waiting_reason_id = $params['waiting_reason_id'];        
            $fileHotelRoomEloquent->waiting_reason_other_message = $params['waiting_reason_other_message'];        
            $fileHotelRoomEloquent->waiting_confirmation_code = $params['waiting_confirmation_code']; 
            $fileHotelRoomEloquent->save();          
        }
      

        return true;

    }
    
    public function updateWlCode(int $id, string $code): bool
    {  
        
        $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()->with([ 
            'fileHotelRoom.fileItinerary'           
        ])->find($id);

        if($fileHotelRoomUnitEloquent->waiting_list == 0){
            throw new \DomainException("the record is not WL");
        }

        
        $fileHotelRoomUnitEloquent->waiting_confirmation_code = $code; 
        $fileHotelRoomUnitEloquent->save();
                
        return true;

    }
    
}
