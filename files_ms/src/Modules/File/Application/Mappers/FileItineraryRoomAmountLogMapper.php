<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileItinearyRoomAmountLog;

use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileRoomAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\FileItineraryRoomAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\Value;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\FileRoomAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\Markup;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryRoomAmountLogEloquentModel;

class FileItineraryRoomAmountLogMapper
{
    public static function fromArray(array $fileItineraryRoomAmountLog): FileItinearyRoomAmountLog
    {
        $fileItineraryRoomAmountLogEloquent = new FileItineraryRoomAmountLogEloquentModel($fileItineraryRoomAmountLog);
        $fileItineraryRoomAmountLogEloquent->id = $fileItineraryRoomAmountLog['id'] ?? null;
        
        return self::fromEloquent($fileItineraryRoomAmountLogEloquent);
    }

    public static function fromEloquent(FileItineraryRoomAmountLogEloquentModel $fileItineraryRoomAmountLogEloquent
    ): FileItinearyRoomAmountLog {
     
        $fileRoomAmountLogMapper = $fileItineraryRoomAmountLogEloquent->fileRoomAmountLog?->toArray() ?? [];
        $fileRoomAmountLogMapper = FileRoomAmountLogMapper::fromArray($fileRoomAmountLogMapper);

        return new FileItinearyRoomAmountLog(
            id: new FileItineraryRoomAmountLogId($fileItineraryRoomAmountLogEloquent->id),
            fileItineraryId: new FileItineraryId($fileItineraryRoomAmountLogEloquent->file_itinerary_id),
            fileRoomAmountLogId: new FileRoomAmountLogId(
                $fileItineraryRoomAmountLogEloquent->file_service_amount_log_id
            ),
            value: new Value($fileItineraryRoomAmountLogEloquent->value),
            markup: new Markup($fileItineraryRoomAmountLogEloquent->markup),
            createdAt: new CreatedAt($fileItineraryRoomAmountLogEloquent->created_at),
            fileRoomAmountLog: new FileRoomAmountLog($fileRoomAmountLogMapper)
        );
    }

    public static function toEloquent(FileItinearyRoomAmountLog
        $fileItineraryServiceAmountLog): FileItineraryRoomAmountLogEloquentModel
    {
        $fileItineraryRoomAmountLogEloquent = new FileItineraryRoomAmountLogEloquentModel();
        if ($fileItineraryServiceAmountLog->id) {
            $fileItineraryRoomAmountLogEloquent = FileItineraryRoomAmountLogEloquentModel::query()
                ->findOrFail($fileItineraryServiceAmountLog->id);
        }
        $fileItineraryRoomAmountLogEloquent
            ->file_itinerary_id = $fileItineraryServiceAmountLog->fileItineraryId->value();
        $fileItineraryRoomAmountLogEloquent
            ->file_room_amount_log_id = $fileItineraryServiceAmountLog->fileRoomAmountLogId->value();
        $fileItineraryRoomAmountLogEloquent->value = $fileItineraryServiceAmountLog->value->value();
        $fileItineraryRoomAmountLogEloquent->markup = $fileItineraryServiceAmountLog->markup->value();
        $fileItineraryRoomAmountLogEloquent->created_at = $fileItineraryServiceAmountLog->createdAt->value();
        return $fileItineraryRoomAmountLogEloquent;
    }

}
