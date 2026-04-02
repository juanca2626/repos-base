<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\FileHotelRoomId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileRoomAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\UserId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\AmountPrevious;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\Amount;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileAmountReason;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileAmountTypeFlag;
use Src\Shared\Domain\Entity;

class FileRoomAmountLog extends Entity
{
    public function __construct(
        public readonly FileRoomAmountLogId $id,
        public readonly FileAmountTypeFlagId $fileAmountTypeFlagId,
        public readonly FileAmountReasonId $fileAmountReasonId,
        public readonly FileHotelRoomId $fileHotelRoomId,
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
            'file_hotel_room_id' => $this->fileHotelRoomId->value(),
            'user_id' => $this->userId->value(),
            'amount_previous' => $this->amountPrevious->value(),
            'amount' => $this->amount->value(),
            'created_at' => $this->createdAt->value(),
            'file_amount_type_flag' => $this->fileAmountTypeFlag->jsonSerialize(),
            'file_amount_reason' => $this->fileAmountReason->jsonSerialize()
        ];
    }

}
