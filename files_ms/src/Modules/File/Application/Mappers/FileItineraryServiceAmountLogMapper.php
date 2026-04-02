<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileItinearyServiceAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileServiceAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\FileItineraryServiceAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\Value;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\FileServiceAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\Markup;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryServiceAmountLogEloquentModel;

class FileItineraryServiceAmountLogMapper
{
    public static function fromArray(array $fileItineraryServiceAmountLog): FileItinearyServiceAmountLog
    {
        $fileItineraryServiceAmountLogEloquent = new FileItineraryServiceAmountLogEloquentModel(
            $fileItineraryServiceAmountLog
        );
        $fileItineraryServiceAmountLogEloquent->id = $fileItineraryServiceAmountLog['id'] ?? null;
        
        return self::fromEloquent($fileItineraryServiceAmountLogEloquent);
    }

    public static function fromEloquent(
        FileItineraryServiceAmountLogEloquentModel $fileItineraryServiceAmountLogEloquent
    ): FileItinearyServiceAmountLog {

        $fileServiceAmountLogMapper = $fileItineraryServiceAmountLogEloquent->fileServiceAmountLog?->toArray() ?? [];
     
        if(count($fileServiceAmountLogMapper) > 0) {
            $fileServiceAmountLogMapper = FileServiceAmountLogMapper::fromArray($fileServiceAmountLogMapper);
        } else {
            $fileServiceAmountLogMapper = new \stdClass();
        }

        return new FileItinearyServiceAmountLog(
            id: new FileItineraryServiceAmountLogId($fileItineraryServiceAmountLogEloquent->id),
            fileItineraryId: new FileItineraryId($fileItineraryServiceAmountLogEloquent->file_itinerary_id),
            fileServiceAmountLogId: new FileServiceAmountLogId(
                $fileItineraryServiceAmountLogEloquent->file_service_amount_log_id
            ),
            value: new Value($fileItineraryServiceAmountLogEloquent->value),
            markup: new Markup($fileItineraryServiceAmountLogEloquent->markup),
            createdAt: new CreatedAt($fileItineraryServiceAmountLogEloquent->created_at),
            fileServiceAmountLog: new FileServiceAmountLog($fileServiceAmountLogMapper)
        );
    }

    public static function toEloquent(FileItinearyServiceAmountLog
        $fileItineraryServiceAmountLog): FileItineraryServiceAmountLogEloquentModel
    {
        $fileItineraryServiceAmountLogEloquent = new FileItineraryServiceAmountLogEloquentModel();
        if ($fileItineraryServiceAmountLog->id) {
            $fileItineraryServiceAmountLogEloquent = FileItineraryServiceAmountLogEloquentModel::query()
                ->findOrFail($fileItineraryServiceAmountLog->id);
        }
        $fileItineraryServiceAmountLogEloquent
            ->file_itinerary_id = $fileItineraryServiceAmountLog->fileItineraryId->value();
        $fileItineraryServiceAmountLogEloquent
            ->file_service_amount_log_id = $fileItineraryServiceAmountLog->fileServiceAmountLogId->value();
        $fileItineraryServiceAmountLogEloquent->value = $fileItineraryServiceAmountLog->value->value();
        $fileItineraryServiceAmountLogEloquent->markup = $fileItineraryServiceAmountLog->markup->value();
        $fileItineraryServiceAmountLogEloquent->created_at = $fileItineraryServiceAmountLog->createdAt->value();
        return $fileItineraryServiceAmountLogEloquent;
    }
}
