<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Domain\Repositories\FileRoomAccommodationRepositoryInterface; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAccommodationEloquentModel; 

class FileRoomAccommodationRepository implements FileRoomAccommodationRepositoryInterface
{
 
    public function create(int $file_hotel_room_unit_id, int $file_passenger_id, string $room_key): bool
    {
        $accommodation = FileRoomAccommodationEloquentModel::where('file_hotel_room_unit_id', $file_hotel_room_unit_id)->where('file_passenger_id',$file_passenger_id)->first();   
        if(!$accommodation){
            $accommodationNew = new FileRoomAccommodationEloquentModel();
            $accommodationNew->file_hotel_room_unit_id = $file_hotel_room_unit_id; 
            $accommodationNew->file_passenger_id = $file_passenger_id;
            $accommodationNew->room_key = $room_key;
            $accommodationNew->save();
            
            $accommodation = FileRoomAccommodationEloquentModel::with([
                'filePassenger'
            ])->find($accommodationNew->id);

            event(new FilePassToOpeEvent($accommodation->filePassenger->file_id));            
        }
        
        return true;
    }


    public function delete(int $file_hotel_room_unit_id): bool
    {

        $accommodation = FileRoomAccommodationEloquentModel::with([
            'filePassenger'
        ])->where('file_hotel_room_unit_id', $file_hotel_room_unit_id)->get(); 
         
        if(count($accommodation)>0){
           
            $fileId = $accommodation[0]->filePassenger->file_id;
           
            $accommodation = FileRoomAccommodationEloquentModel::with([
                'filePassenger'
            ])->where('file_hotel_room_unit_id', $file_hotel_room_unit_id)->delete();

            event(new FilePassToOpeEvent($fileId));
                                                
        }

        return true;
    }


}
