<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\VipMapper;
use Src\Modules\File\Domain\Model\Vip;
use Src\Modules\File\Domain\Repositories\FilePassengerModifyPaxRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerModifyPaxEloquentModel;

class FilePassengerModifyPaxRepository implements FilePassengerModifyPaxRepositoryInterface
{
    public function create(int $fileId): bool
    {
        $filePassengers = FilePassengerEloquentModel::query()
        ->where('file_id', '=', $fileId) 
        ->get();

        $filePassengerModifyPaxEloquent = FilePassengerModifyPaxEloquentModel::query()->whereHas('filePassenger', function ($query) use ($fileId) {
            $query->where('file_id', '=', $fileId);            
        })->get();

        if($filePassengerModifyPaxEloquent->count() == 0){
            
            foreach($filePassengers as $filePassenger){
                FilePassengerModifyPaxEloquentModel::create([ 
                    'file_passenger_id' => $filePassenger->id,
                    'name' => $filePassenger->name,
                    'surnames' => $filePassenger->surnames,
                    'date_birth' => $filePassenger->date_birth,
                    'type' => $filePassenger->type,
                    'suggested_room_type' => $filePassenger->suggested_room_type,
                    'accommodation' => $filePassenger->accommodation,
                    'cost_by_passenger' => $filePassenger->cost_by_passenger
                ]);
            }
        }

        return false;
    }

    public function reset(int $fileId): bool
    {
        $filePassengers = FilePassengerEloquentModel::query()
        ->where('file_id', '=', $fileId) 
        ->get();

        FilePassengerModifyPaxEloquentModel::query()->whereHas('filePassenger', function ($query) use ($fileId) {
            $query->where('file_id', '=', $fileId);            
        })->delete();
                     
        foreach($filePassengers as $filePassenger){
            FilePassengerModifyPaxEloquentModel::create([ 
                'file_passenger_id' => $filePassenger->id,
                'name' => $filePassenger->name,
                'surnames' => $filePassenger->surnames,
                'date_birth' => $filePassenger->date_birth,
                'type' => $filePassenger->type,
                'suggested_room_type' => $filePassenger->suggested_room_type,
                'accommodation' => $filePassenger->accommodation,
                'cost_by_passenger' => $filePassenger->cost_by_passenger
            ]);
        }
     

        return false;
    }    
    
 
    /**
     * @param int $id
     * @param File $userData
     * @return bool
     */
    public function update(int $fileId, array $params): bool
    {
        FilePassengerModifyPaxEloquentModel::query()->whereHas('filePassenger', function ($query) use ($fileId) {
            $query->where('file_id', '=', $fileId);            
        })->delete();

        foreach($params as $filePassenger){
            FilePassengerModifyPaxEloquentModel::create([ 
                'file_passenger_id' => $filePassenger['file_passenger_id'],
                'name' => $filePassenger['name'],
                'surnames' => $filePassenger['surnames'],
                'date_birth' => $filePassenger['date_birth'],
                'type' => $filePassenger['type'],
                'suggested_room_type' => $filePassenger['suggested_room_type'],
                'accommodation' => json_encode($filePassenger['accommodation']),
                'cost_by_passenger' => $filePassenger['cost_by_passenger']
            ]);
        }

        return false;
    }
  
    public function searchAll(int $fileId): array
    {
        $filePassengerModifyPaxEloquent = FilePassengerModifyPaxEloquentModel::query()->whereHas('filePassenger', function ($query) use ($fileId) {
            $query->where('file_id', '=', $fileId);            
        })->get()->toArray();             
        
        foreach($filePassengerModifyPaxEloquent as $index => $filePassenger){
            if($filePassenger['accommodation']){
               $passengerExtructure = [];
               $accommodation = json_decode($filePassenger['accommodation']); 
               if(is_array($accommodation)){
                    foreach($accommodation as $passenger){
                            $filePassengerEloquent = FilePassengerEloquentModel::find($passenger);
            
                            array_push($passengerExtructure, [
                                'id' => $filePassengerEloquent->id,
                                'name' => $filePassengerEloquent->name,
                                'surnames' => $filePassengerEloquent->surnames
                            ]);
                    }
                    $filePassengerModifyPaxEloquent[$index]['accommodation'] = $passengerExtructure;
               }
            }
        }

        return $filePassengerModifyPaxEloquent;
    }

}
