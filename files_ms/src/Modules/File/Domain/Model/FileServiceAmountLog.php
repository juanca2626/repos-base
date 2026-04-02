<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileService\FileServiceId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileServiceAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\UserId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\AmountPrevious;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\Amount;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileAmountReason;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileAmountTypeFlag;
use Src\Shared\Domain\Entity;

class FileServiceAmountLog extends Entity
{
    public function __construct(
        public readonly FileServiceAmountLogId $id,
        public readonly FileAmountTypeFlagId $fileAmountTypeFlagId,
        public readonly FileAmountReasonId $fileAmountReasonId,
        public readonly FileServiceId $fileServiceId,
        public readonly UserId $userId,
        public readonly AmountPrevious $amountPrevious,
        public readonly Amount $amount,
        public readonly CreatedAt $createdAt,
        public readonly FileAmountTypeFlag $fileAmountTypeFlag,
        public readonly FileAmountReason $fileAmountReason
    ) {
    }

    public function getDate(): string
    {
        return Carbon::parse($this->createdAt)->format('d/m/Y H:i:s');
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_amount_type_flag_id' => $this->fileAmountTypeFlagId->value(),
            'file_amount_reason_id' => $this->fileAmountReasonId->value(),
            'file_service_id' => $this->fileServiceId->value(),
            'user_id' => $this->userId->value(),
            'amount_previous' => $this->amountPrevious->value(),
            'amount' => $this->amount->value(),
            'created_at' => $this->createdAt->value(),
            'file_amount_type_flag' => $this->fileAmountReason->jsonSerialize(),
            'file_amount_reason' => $this->fileAmountReason->jsonSerialize(),
        ];
    }

}
