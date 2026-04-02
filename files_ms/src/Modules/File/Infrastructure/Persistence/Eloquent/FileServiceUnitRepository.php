<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Repositories\FileServiceUnitRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel; 

class FileServiceUnitRepository implements FileServiceUnitRepositoryInterface
{
 
    public function updateStatus(int $id, int $status): bool
    {  
        $fileHotelRoomUnitEloquent = FileServiceUnitEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->status = $status;
        $fileHotelRoomUnitEloquent->save();
        
        return true;
    }

    public function findById(int $id): array
    {       
        $fileHotelRoomUnitEloquent = FileServiceUnitEloquentModel::query()->findOrFail($id);
 
        return $fileHotelRoomUnitEloquent->toArray();
    }

    public function updateAmountCost(int $id, float $newAmountCost): bool
    {  
        $fileHotelRoomUnitEloquent = FileServiceUnitEloquentModel::query()->findOrFail($id);
        $fileHotelRoomUnitEloquent->amount_cost = $newAmountCost;
        $fileHotelRoomUnitEloquent->save();

        return true;
    }  

    public function updateConfirmationCode(int $id, string $code): bool
    {  
        
        $fileServiceUnitEloquentModel = FileServiceUnitEloquentModel::query()->find($id);

        $fileServiceUnitEloquentModel->confirmation_status = true;        
        $fileServiceUnitEloquentModel->waiting_list = false;
        $fileServiceUnitEloquentModel->confirmation_code = $code;
        $fileServiceUnitEloquentModel->save();
       
        // validamos si totos los units estan confirmados.
        $fileServiceCompositionEloquentModel = FileServiceCompositionEloquentModel::query()->with(['units'])->find($fileServiceUnitEloquentModel->file_service_composition_id);
         
        $assing_all_confirmation_code = true; //validamos que todos los units del composition su estado este confirmado
        foreach($fileServiceCompositionEloquentModel->units as $unit)
        {
            if($unit->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileServiceCompositionEloquentModel->confirmation_status = true;        
            $fileServiceCompositionEloquentModel->waiting_list = false;
            $fileServiceCompositionEloquentModel->confirmation_code = $code;
            $fileServiceCompositionEloquentModel->save();

        }
       
        // validamos si totos los compositions estan confirmados.
        $fileServiceEloquentModel = FileServiceEloquentModel::query()->with(['compositions'])->find($fileServiceCompositionEloquentModel->file_service_id);
         
        $assing_all_confirmation_code = true; //validamos que todos los units del composition su estado este confirmado
        foreach($fileServiceEloquentModel->compositions as $composition)
        {
            if($composition->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileServiceEloquentModel->confirmation_status = true;     
            $fileServiceEloquentModel->save();
        }        


        // validamos si totos los services estan confirmados.
        $fileItineryEloquentModel = FileItineraryEloquentModel::query()->with(['services'])->find($fileServiceEloquentModel->file_itinerary_id);
         
        $assing_all_confirmation_code = true; //validamos que todos los units del composition su estado este confirmado
        foreach($fileItineryEloquentModel->services as $service)
        {
            if($service->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileItineryEloquentModel->confirmation_status = true;    
            $fileItineryEloquentModel->save();
        }  

        return true;

    }

    public function updateRqWl(int $id, array $params): bool
    {  
        
        $fileServiceUnitEloquentModel = FileServiceUnitEloquentModel::query()->find($id);

        if($fileServiceUnitEloquentModel->confirmation_status != 0){
            throw new \DomainException("the record is not RQ");
        }

        $fileServiceUnitEloquentModel->waiting_list = true;
        $fileServiceUnitEloquentModel->waiting_reason_id = $params['waiting_reason_id'];        
        $fileServiceUnitEloquentModel->waiting_reason_other_message = $params['waiting_reason_other_message'];        
        $fileServiceUnitEloquentModel->waiting_confirmation_code = $params['waiting_confirmation_code']; 
        $fileServiceUnitEloquentModel->save();
        
        $fileServiceCompositionEloquentModel = FileServiceCompositionEloquentModel::query()->with([ 
            'units',            
        ])->find($fileServiceUnitEloquentModel->file_service_composition_id);

        
        $status_all = [
            'ok' => 9,
            'rq' => 9,
            'wl' => 9
        ];  

        foreach($fileServiceCompositionEloquentModel->units as $unit)
        {
            if($unit->confirmation_status == true)
            {
                $status_all['ok'] = 3;

            }else{
                if($unit->waiting_list == true)
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

        if($status_new == "wl" and $fileServiceCompositionEloquentModel->confirmation_status == 0)
        {                
            $fileServiceCompositionEloquentModel->waiting_list = true;
            $fileServiceCompositionEloquentModel->waiting_reason_id = $params['waiting_reason_id'];        
            $fileServiceCompositionEloquentModel->waiting_reason_other_message = $params['waiting_reason_other_message'];        
            $fileServiceCompositionEloquentModel->waiting_confirmation_code = $params['waiting_confirmation_code']; 
            $fileServiceCompositionEloquentModel->save();          
        }
      

        return true;

    }
    
    public function updateWlCode(int $id, string $code): bool
    {  
        
        $fileServiceUnitEloquentModel = FileServiceUnitEloquentModel::query()->find($id);

        if($fileServiceUnitEloquentModel->waiting_list == 0){
            throw new \DomainException("the record is not WL");
        }

        
        $fileServiceUnitEloquentModel->waiting_confirmation_code = $code; 
        $fileServiceUnitEloquentModel->save();
                
        return true;

    }
   
}
