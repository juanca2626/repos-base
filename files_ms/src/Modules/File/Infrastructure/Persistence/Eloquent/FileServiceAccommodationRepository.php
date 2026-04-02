<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Domain\Repositories\FileServiceAccommodationRepositoryInterface; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAccommodationEloquentModel; 

class FileServiceAccommodationRepository implements FileServiceAccommodationRepositoryInterface
{
 
    public function create(int $file_service_unit_id, int $file_passenger_id, string $room_key): bool
    {

        $accommodation = FileServiceAccommodationEloquentModel::where('file_service_unit_id', $file_service_unit_id)->where('file_passenger_id',$file_passenger_id)->first();   
        if(!$accommodation){
            $accommodationNew = new FileServiceAccommodationEloquentModel();
            $accommodationNew->file_service_unit_id = $file_service_unit_id; 
            $accommodationNew->file_passenger_id = $file_passenger_id;
            $accommodationNew->room_key = $room_key;
            $accommodationNew->save();

            $accommodation = FileServiceAccommodationEloquentModel::with([
                'filePassenger'
            ])->find($accommodationNew->id);

            event(new FilePassToOpeEvent($accommodation->filePassenger->file_id));  
        }
         
        return true;
    }

    public function delete(int $file_service_unit_id): bool
    {

        $accommodation = FileServiceAccommodationEloquentModel::with([
            'filePassenger'
        ])->where('file_service_unit_id', $file_service_unit_id)->first(); 
        
        if($accommodation){
            $fileId = $accommodation->filePassenger->file_id;
            $accommodation->delete();
            event(new FilePassToOpeEvent($fileId));
        }
         
        return true;
    }

}
