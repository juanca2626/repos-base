<?php

namespace Src\Modules\File\Application\Mappers;
 
use Src\Modules\File\Domain\Model\FileRoomAccomodation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAccomodation\FilePassenger;
use Src\Modules\File\Domain\ValueObjects\FileRoomAccomodation\RoomKey; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAccommodationEloquentModel; 

class FileRoomAccommodationMapper
{
    public static function fromArray(array $fileRoomAccomodation): FileRoomAccomodation
    {
        $fileRoomAccomodationEloquentModel = new FileRoomAccommodationEloquentModel($fileRoomAccomodation);
        $fileRoomAccomodationEloquentModel->id = $fileRoomAccomodation['id'] ?? null;
 
        // if(isset($fileRoomAccomodation['filePassenger'])){
        //     $fileRoomAccomodationEloquentModel->filePassenger = collect($fileRoomAccomodation['filePassenger']);
        // }        

        return self::fromEloquent($fileRoomAccomodationEloquentModel);
    }

    public static function fromEloquent(FileRoomAccommodationEloquentModel $fileRoomAccomodationEloquentModel
    ): FileRoomAccomodation {

        $filePassenger = collect();      
        if($fileRoomAccomodationEloquentModel->filePassenger?->toArray()){
           $filePassenger = $fileRoomAccomodationEloquentModel->filePassenger?->toArray();
           $filePassenger = FilePassengerMapper::fromArray($filePassenger);
        }

        return new FileRoomAccomodation(
            id: $fileRoomAccomodationEloquentModel->id,
            fileHotelRoomUnitId: new FileHotelRoomUnitId($fileRoomAccomodationEloquentModel->file_hotel_room_unit_id),
            filePassengerId: new FilePassengerId($fileRoomAccomodationEloquentModel->file_passenger_id),
            roomKey: new RoomKey($fileRoomAccomodationEloquentModel->room_key),
            filePassenger: new FilePassenger($filePassenger) 
        );
    }

    public static function toEloquent(FileRoomAccomodation $fileRoomAccomodation): FileRoomAccommodationEloquentModel
    {
        $fileRoomAccomodationEloquent = new FileRoomAccommodationEloquentModel();
        if ($fileRoomAccomodation->id) {
            $fileRoomAccomodationEloquent = FileRoomAccommodationEloquentModel::query()->findOrFail($fileRoomAccomodation->id);
        }
        $fileRoomAccomodationEloquent->file_hotel_room_unit_id = $fileRoomAccomodation->fileHotelRoomUnitId->value();
        $fileRoomAccomodationEloquent->file_passenger_id = $fileRoomAccomodation->filePassengerId->value();
        $fileRoomAccomodationEloquent->room_key = $fileRoomAccomodation->roomKey->value(); 
        return $fileRoomAccomodationEloquent;
    }
}
