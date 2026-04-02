<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileServiceAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileServiceAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\UserId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\AmountPrevious;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\Amount;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileAmountTypeFlag;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileAmountReason;


use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceAmountLogEloquentModel;

class FileServiceAmountLogMapper
{

    public static function fromRequestCreate(array $fileServiceAmountLog): FileServiceAmountLog
    {
        $id = NULL;
        $file_amount_type_flag_id = $fileServiceAmountLog['file_amount_type_flag_id'] ? $fileServiceAmountLog['file_amount_type_flag_id'] : 1;
        $file_amount_reason_id = $fileServiceAmountLog['file_amount_reason_id'] ? $fileServiceAmountLog['file_amount_reason_id'] : 8;
        $file_service_id = $fileServiceAmountLog['file_service_id'] ? $fileServiceAmountLog['file_service_id'] : 0;
        $user_id = $fileServiceAmountLog['user_id'] ? $fileServiceAmountLog['user_id'] : 1;
        $amount_previous = $fileServiceAmountLog['amount_previous'] ? $fileServiceAmountLog['amount_previous'] : 0;
        $amount = $fileServiceAmountLog['amount'] ? $fileServiceAmountLog['amount'] : 0;
        $created_at = isset($fileServiceAmountLog['created_at']) ? $fileServiceAmountLog['created_at'] : date('Y-m-d H:i:s'); 
        
        return new FileServiceAmountLog(
            id: new FileServiceAmountLogId($id),
            fileAmountTypeFlagId: new FileAmountTypeFlagId($file_amount_type_flag_id),
            fileAmountReasonId: new FileAmountReasonId($file_amount_reason_id),
            fileServiceId: new FileServiceId($file_service_id),
            userId: new UserId($user_id),
            amountPrevious: new AmountPrevious($amount_previous),
            amount: new Amount($amount),
            createdAt: new CreatedAt($created_at),
            fileAmountTypeFlag: new FileAmountTypeFlag(new \stdClass()),
            fileAmountReason: new FileAmountReason(new \stdClass())
        );
    }

    public static function fromArray(array $fileServiceAmountLog): FileServiceAmountLog
    {
        $fileServiceAmountLogEloquent = new FileServiceAmountLogEloquentModel($fileServiceAmountLog);
        $fileServiceAmountLogEloquent->id = $fileServiceAmountLog['id'] ?? null;
        
        return self::fromEloquent($fileServiceAmountLogEloquent);
    }

    public static function fromEloquent(FileServiceAmountLogEloquentModel $fileServiceAmountLogEloquent
    ): FileServiceAmountLog {
        
        $fileAmountReason = $fileServiceAmountLogEloquent->fileAmountReason?->toArray() ?? [];
        if(count($fileAmountReason) > 0) {
            $fileAmountReason = FileAmountReasonMapper::fromArray($fileAmountReason);
        } else {
            $fileAmountReason = new \stdClass();
        }

        $fileAmountTypeFlag = $fileServiceAmountLogEloquent->fileAmountTypeFlag?->toArray() ?? [];
        if(count($fileAmountTypeFlag) > 0) {
            $fileAmountTypeFlag = FileAmountTypeFlagMapper::fromArray($fileAmountTypeFlag);
        } else {
            $fileAmountTypeFlag = new \stdClass();
        }

        return new FileServiceAmountLog(
            id: new FileServiceAmountLogId($fileServiceAmountLogEloquent->id),
            fileAmountTypeFlagId: new FileAmountTypeFlagId($fileServiceAmountLogEloquent->file_amount_type_flag_id),
            fileAmountReasonId: new FileAmountReasonId($fileServiceAmountLogEloquent->file_amount_reason_id),
            fileServiceId: new FileServiceId($fileServiceAmountLogEloquent->file_service_id),
            userId: new UserId($fileServiceAmountLogEloquent->user_id),
            amountPrevious: new AmountPrevious($fileServiceAmountLogEloquent->amount_previous),
            amount: new Amount($fileServiceAmountLogEloquent->amount),
            createdAt: new CreatedAt($fileServiceAmountLogEloquent->created_at),
            fileAmountTypeFlag: new FileAmountTypeFlag($fileAmountTypeFlag),
            fileAmountReason: new FileAmountReason($fileAmountReason)
        );
    }

    public static function toEloquent(FileServiceAmountLog $fileServiceAmountLog): FileServiceAmountLogEloquentModel
    {
        $fileServiceAmountLogEloquent = new FileServiceAmountLogEloquentModel();
        if ($fileServiceAmountLog->id->value()) {
            $fileServiceAmountLogEloquent = FileServiceAmountLogEloquentModel::query()
                ->findOrFail($fileServiceAmountLog->id->value());
        }
        $fileServiceAmountLogEloquent->file_amount_type_flag_id = $fileServiceAmountLog->fileAmountTypeFlagId->value();
        $fileServiceAmountLogEloquent->file_amount_reason_id = $fileServiceAmountLog->fileAmountReasonId->value();
        $fileServiceAmountLogEloquent->file_service_id = $fileServiceAmountLog->fileServiceId->value();
        $fileServiceAmountLogEloquent->user_id = $fileServiceAmountLog->userId->value();
        $fileServiceAmountLogEloquent->amount_previous = $fileServiceAmountLog->amountPrevious->value();
        $fileServiceAmountLogEloquent->amount = $fileServiceAmountLog->amount->value();

        return $fileServiceAmountLogEloquent;
    }
}
