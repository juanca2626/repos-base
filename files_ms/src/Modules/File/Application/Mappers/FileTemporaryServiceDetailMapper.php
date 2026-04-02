<?php

namespace Src\Modules\File\Application\Mappers;
use Illuminate\Http\Request;  
use Src\Modules\File\Domain\Model\FileTemporaryServiceDetail;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileTemporaryServiceId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail\Itinerary;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail\Skeleton;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail\LanguageId; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceDetailEloquentModel; 

class FileTemporaryServiceDetailMapper
{  
    public static function fromArray(array $temporaryServiceDetail): FileTemporaryServiceDetail
    { 
        $fileTemporaryServiceDetailEloquentModel = new FileTemporaryServiceDetailEloquentModel($temporaryServiceDetail);
        $fileTemporaryServiceDetailEloquentModel->id = $temporaryServiceDetail['id'] ?? null; 
        return self::fromEloquent($fileTemporaryServiceDetailEloquentModel);
    }

    public static function fromEloquent(FileTemporaryServiceDetailEloquentModel $fileTemporaryServiceDetailEloquentModel): FileTemporaryServiceDetail
    {

        return new FileTemporaryServiceDetail(
            id: $fileTemporaryServiceDetailEloquentModel->id,
            fileTemporaryServiceId: new FileTemporaryServiceId($fileTemporaryServiceDetailEloquentModel->file_temporary_service_id),
            languageId: new LanguageId($fileTemporaryServiceDetailEloquentModel->language_id),
            itinerary: new Itinerary($fileTemporaryServiceDetailEloquentModel->itinerary),
            skeleton: new Skeleton($fileTemporaryServiceDetailEloquentModel->skeleton)
        );
    }

    public static function toEloquent(FileTemporaryServiceDetail $temporaryServiceDetail): FileTemporaryServiceDetailEloquentModel
    {
        $fileItineraryDetailEloquentModel = new FileTemporaryServiceDetailEloquentModel();
        if ($temporaryServiceDetail->id) {
            $fileItineraryDetailEloquentModel = FileTemporaryServiceDetailEloquentModel::query()->findOrFail($temporaryServiceDetail->id);
        }
        $fileItineraryDetailEloquentModel->file_temporary_service_id = $temporaryServiceDetail->fileTemporaryServiceId->value(); 
        $fileItineraryDetailEloquentModel->language_id = $temporaryServiceDetail->languageId->value();
        $fileItineraryDetailEloquentModel->itinerary = $temporaryServiceDetail->itinerary->value();
        $fileItineraryDetailEloquentModel->skeleton = $temporaryServiceDetail->skeleton->value(); 
       
        return $fileItineraryDetailEloquentModel;
    }
}
