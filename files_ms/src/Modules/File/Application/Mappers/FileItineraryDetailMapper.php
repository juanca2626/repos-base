<?php

namespace Src\Modules\File\Application\Mappers;
use Illuminate\Http\Request; 
use Src\Modules\File\Domain\Model\FileItineraryDetail; 
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDetail\Itinerary;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDetail\Skeleton;
use Src\Modules\File\Domain\ValueObjects\FileItineraryDetail\LanguageId; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryDetailEloquentModel; 

class FileItineraryDetailMapper
{  
    public static function fromArray(array $itineraryDetail): FileItineraryDetail
    { 
        $fileItineraryDetailEloquentModel = new FileItineraryDetailEloquentModel($itineraryDetail);
        $fileItineraryDetailEloquentModel->id = $itineraryDetail['id'] ?? null;
        $fileItineraryDetailEloquentModel->accommodations = collect();
        return self::fromEloquent($fileItineraryDetailEloquentModel);
    }

    public static function fromEloquent(FileItineraryDetailEloquentModel $fileItineraryFlightEloquentModel): FileItineraryDetail
    {

        return new FileItineraryDetail(
            id: $fileItineraryFlightEloquentModel->id,
            fileItineraryId: new FileItineraryId($fileItineraryFlightEloquentModel->file_itinerary_id),
            languageId: new LanguageId($fileItineraryFlightEloquentModel->language_id),
            itinerary: new Itinerary($fileItineraryFlightEloquentModel->itinerary),
            skeleton: new Skeleton($fileItineraryFlightEloquentModel->skeleton)
        );
    }

    public static function toEloquent(FileItineraryDetail $fileItineraryDetail): FileItineraryDetailEloquentModel
    {
        $fileItineraryDetailEloquentModel = new FileItineraryDetailEloquentModel();
        if ($fileItineraryDetail->id) {
            $fileItineraryDetailEloquentModel = FileItineraryDetailEloquentModel::query()->findOrFail($fileItineraryDetail->id);
        }
        $fileItineraryDetailEloquentModel->file_itinerary_id = $fileItineraryDetail->fileItineraryId->value(); 
        $fileItineraryDetailEloquentModel->language_id = $fileItineraryDetail->languageId->value();
        $fileItineraryDetailEloquentModel->itinerary = $fileItineraryDetail->itinerary->value();
        $fileItineraryDetailEloquentModel->skeleton = $fileItineraryDetail->skeleton->value(); 
       
        return $fileItineraryDetailEloquentModel;
    }
}
