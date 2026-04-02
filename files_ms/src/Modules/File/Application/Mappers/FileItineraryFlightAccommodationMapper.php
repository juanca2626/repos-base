<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileItineraryFlightAccomodation;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\FileItineraryFlightId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlightAccomodation\FilePassenger;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryFlightAccommodationEloquentModel; 

class FileItineraryFlightAccommodationMapper
{
    public static function fromArray(array $fileRoomAccomodation): FileItineraryFlightAccomodation
    {
        $fileItineraryFlightAccommodationEloquentModel = new FileItineraryFlightAccommodationEloquentModel($fileRoomAccomodation);
        $fileItineraryFlightAccommodationEloquentModel->id = $fileRoomAccomodation['id'] ?? null;
 
        // if(isset($fileRoomAccomodation['filePassenger'])){
        //     $fileItineraryFlightAccommodationEloquentModel->filePassenger = collect($fileRoomAccomodation['filePassenger']);
        // }        

        return self::fromEloquent($fileItineraryFlightAccommodationEloquentModel);
    }

    public static function fromEloquent(FileItineraryFlightAccommodationEloquentModel $fileItineraryFlightAccommodationEloquentModel
    ): FileItineraryFlightAccomodation {

        $filePassenger = collect();      
        if($fileItineraryFlightAccommodationEloquentModel->filePassenger?->toArray()){
           $filePassenger = $fileItineraryFlightAccommodationEloquentModel->filePassenger?->toArray();
           $filePassenger = FilePassengerMapper::fromArray($filePassenger);
        }

        return new FileItineraryFlightAccomodation(
            id: $fileItineraryFlightAccommodationEloquentModel->id,
            fileItineraryFlightId: new FileItineraryFlightId($fileItineraryFlightAccommodationEloquentModel->file_itinerary_flight_id),
            filePassengerId: new FilePassengerId($fileItineraryFlightAccommodationEloquentModel->file_passenger_id), 
            filePassenger: new FilePassenger($filePassenger) 
        );
    }

    public static function toEloquent(FileItineraryFlightAccomodation $fileRoomAccomodation): FileItineraryFlightAccommodationEloquentModel
    {
        $fileItineraryFlightAccommodationEloquentModel = new FileItineraryFlightAccommodationEloquentModel();
        if ($fileRoomAccomodation->id) {
            $fileItineraryFlightAccommodationEloquentModel = FileItineraryFlightAccommodationEloquentModel::query()->findOrFail($fileRoomAccomodation->id);
        }
        $fileItineraryFlightAccommodationEloquentModel->file_itinerary_flight_id = $fileRoomAccomodation->fileItineraryFlightId->value();
        $fileItineraryFlightAccommodationEloquentModel->file_passenger_id = $fileRoomAccomodation->filePassengerId->value(); 
        return $fileItineraryFlightAccommodationEloquentModel;
    }
}
