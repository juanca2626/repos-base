<?php

namespace Src\Modules\File\Application\Mappers;
 
use Src\Modules\File\Domain\Model\FileRoomAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileHotelRoomId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\UserId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\AmountPrevious;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\Amount;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileAmountReason;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileRoomAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileAmountTypeFlag;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAmountLogEloquentModel;

class FileRoomAmountLogMapper
{
    
    public static function fromArray(array $fileRoomAmountLog): FileRoomAmountLog
    {
        $fileRoomAmountLogEloquent = new FileRoomAmountLogEloquentModel($fileRoomAmountLog);
        $fileRoomAmountLogEloquent->id = $fileRoomAmountLog['id'] ?? null;
 
        return self::fromEloquent($fileRoomAmountLogEloquent);
    }

    public static function fromEloquent(FileRoomAmountLogEloquentModel $fileRoomAmountLogEloquent
    ): FileRoomAmountLog {
        $fileAmountReason = $fileRoomAmountLogEloquent->fileAmountReason?->toArray() ?? [];
        $fileAmountReason = FileAmountReasonMapper::fromArray($fileAmountReason);

        $fileAmountTypeFlag = $fileRoomAmountLogEloquent->fileAmountTypeFlag?->toArray() ?? [];
        $fileAmountTypeFlag = FileAmountTypeFlagMapper::fromArray($fileAmountTypeFlag);

        return new FileRoomAmountLog(
            id:  new FileRoomAmountLogId($fileRoomAmountLogEloquent->id),
            fileAmountTypeFlagId: new FileAmountTypeFlagId($fileRoomAmountLogEloquent->file_amount_type_flag_id),
            fileAmountReasonId: new FileAmountReasonId($fileRoomAmountLogEloquent->file_amount_reason_id),
            fileHotelRoomId: new FileHotelRoomId($fileRoomAmountLogEloquent->file_hotel_room_id),
            userId: new UserId($fileRoomAmountLogEloquent->user_id),
            amountPrevious: new AmountPrevious($fileRoomAmountLogEloquent->amount_previous),
            amount: new Amount($fileRoomAmountLogEloquent->amount),
            createdAt: new CreatedAt($fileRoomAmountLogEloquent->created_at),
            fileAmountTypeFlag: new FileAmountTypeFlag($fileAmountTypeFlag),
            fileAmountReason: new FileAmountReason($fileAmountReason)
        );
    }


    public static function toEloquent(FileRoomAmountLog $fileRoomAmountLog): FileRoomAmountLogEloquentModel
    {
        $fileRoomAmountLogEloquent = new FileRoomAmountLogEloquentModel();

        if ($fileRoomAmountLog->id->value()) {
            $fileRoomAmountLogEloquent = FileRoomAmountLogEloquentModel::query()
                ->findOrFail($fileRoomAmountLog->id->value());
        }
      
        $fileRoomAmountLogEloquent->file_amount_type_flag_id = $fileRoomAmountLog->fileAmountTypeFlagId->value();
        $fileRoomAmountLogEloquent->file_amount_reason_id = $fileRoomAmountLog->fileAmountReasonId->value();
        $fileRoomAmountLogEloquent->file_hotel_room_id = $fileRoomAmountLog->fileHotelRoomId->value();
        $fileRoomAmountLogEloquent->user_id = $fileRoomAmountLog->userId->value();
        $fileRoomAmountLogEloquent->amount_previous = $fileRoomAmountLog->amountPrevious->value();
        $fileRoomAmountLogEloquent->amount = $fileRoomAmountLog->amount->value();

        return $fileRoomAmountLogEloquent;
    }
}
