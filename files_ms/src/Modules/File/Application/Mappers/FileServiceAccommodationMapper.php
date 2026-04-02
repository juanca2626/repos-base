<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileServiceAccomodation;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAccomodation\RoomKey;
use Src\Modules\File\Domain\ValueObjects\FileServiceAccomodation\Passenger;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\FileServiceUnitId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAccommodationEloquentModel;  

class FileServiceAccommodationMapper
{

    public static function fromRequestCreate(array $accommodation): FileServiceAccomodation
    {
        $file_service_unit_id = isset($accommodation['file_service_unit_id']) ? $accommodation['file_service_unit_id'] : 0;
        $file_passenger_id = $accommodation['file_passenger_id'] ? $accommodation['file_passenger_id'] :  1;
        $room_key = $accommodation['room_key'] ? $accommodation['room_key'] :  NULL;
        $passenger = collect(); 

        return new FileServiceAccomodation(
            id: NULL,
            fileServiceUnitId: new FileServiceUnitId($file_service_unit_id),
            filePassengerId: new FilePassengerId($file_passenger_id),
            roomKey: new RoomKey($room_key),
            passenger: new Passenger($passenger)         
        );
    }
 
    public static function fromArray($fileServiceAccommodation): FileServiceAccomodation
    {       
        $fileServiceAccommodationEloquentModel = new FileServiceAccommodationEloquentModel($fileServiceAccommodation);
        $fileServiceAccommodationEloquentModel->id = $fileServiceAccommodation['id'] ?? null;    
        
        return self::fromEloquent($fileServiceAccommodationEloquentModel);
    }

    public static function fromEloquent(FileServiceAccommodationEloquentModel $fileServiceAccommodation): FileServiceAccomodation
    {            
        
        $filePassenger = collect();      
        if($fileServiceAccommodation->filePassenger?->toArray()){
           $filePassenger = $fileServiceAccommodation->filePassenger?->toArray();
           $filePassenger = FilePassengerMapper::fromArray($filePassenger);
        }

        return new FileServiceAccomodation(
            id: $fileServiceAccommodation->id,
            fileServiceUnitId: new FileServiceUnitId($fileServiceAccommodation->file_service_unit_id),
            filePassengerId: new FilePassengerId($fileServiceAccommodation->file_passenger_id),
            roomKey: new RoomKey($fileServiceAccommodation->room_key),
            passenger: new Passenger($filePassenger)         
        );
    }

    public static function toEloquent(FileServiceAccomodation $fileServiceAccommodation): FileServiceAccommodationEloquentModel
    {
        $fileServiceAccommodationEloquent = new FileServiceAccommodationEloquentModel();
        if ($fileServiceAccommodation->id) {
            $fileServiceAccommodationEloquent = FileServiceAccommodationEloquentModel::query()->findOrFail($fileServiceAccommodation->id);
        }
        $fileServiceAccommodationEloquent->file_service_unit_id = $fileServiceAccommodation->fileServiceUnitId->value();
        $fileServiceAccommodationEloquent->file_passenger_id = $fileServiceAccommodation->filePassengerId->value();
        $fileServiceAccommodationEloquent->room_key = $fileServiceAccommodation->roomKey->value(); 
  
        return $fileServiceAccommodationEloquent;
    }
      
}
