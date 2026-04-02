<?php

namespace Src\Modules\File\Application\Mappers;
use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileItineraryAccommodation; 
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation\FilePassengerId; 
use Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation\FilePassenger; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryAccommodationEloquentModel; 

class FileItineraryAccommodationMapper
{  
    public static function fromArray(array $itineraryAccommodation): FileItineraryAccommodation
    { 
        $fileItineraryAccommodationEloquentModel = new FileItineraryAccommodationEloquentModel($itineraryAccommodation);
        $fileItineraryAccommodationEloquentModel->id = $itineraryAccommodation['id'] ?? null;
        $fileItineraryAccommodationEloquentModel->accommodations = collect();
        return self::fromEloquent($fileItineraryAccommodationEloquentModel);
    }

    public static function fromEloquent(FileItineraryAccommodationEloquentModel $fileItineraryAccommodationEloquentModel): FileItineraryAccommodation
    {
 
        $filePassenger = collect();      
        if($fileItineraryAccommodationEloquentModel->filePassenger?->toArray()){
           $filePassenger = $fileItineraryAccommodationEloquentModel->filePassenger?->toArray();
           $filePassenger = FilePassengerMapper::fromArray($filePassenger);
        }


        return new FileItineraryAccommodation(
            id: $fileItineraryAccommodationEloquentModel->id,
            fileItineraryId: new FileItineraryId($fileItineraryAccommodationEloquentModel->file_itinerary_id),
            filePassengerId: new FilePassengerId($fileItineraryAccommodationEloquentModel->file_passenger_id),
            filePassenger: new FilePassenger($filePassenger)  
        );
    }

    public static function toEloquent(FileItineraryAccommodation $fileItineraryAccommodation): FileItineraryAccommodationEloquentModel
    {
        $fileItineraryAccommodationEloquentModel = new FileItineraryAccommodationEloquentModel();
        if ($fileItineraryAccommodation->id) {
            $fileItineraryAccommodationEloquentModel = FileItineraryAccommodationEloquentModel::query()->findOrFail($fileItineraryAccommodation->id);
        }
        $fileItineraryAccommodationEloquentModel->file_itinerary_id = $fileItineraryAccommodation->fileItineraryId->value(); 
        $fileItineraryAccommodationEloquentModel->file_passenger_id = $fileItineraryAccommodation->filePassengerId->value(); 
       
        return $fileItineraryAccommodationEloquentModel;
    }
}
