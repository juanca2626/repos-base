<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon; 
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog\FileRoomAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\FileItineraryRoomAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\Value;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\Markup;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\FileRoomAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog\CreatedAt;

use Src\Shared\Domain\Entity;

class FileItinearyRoomAmountLog extends Entity
{
    public function __construct(
        public readonly FileItineraryRoomAmountLogId $id,
        public readonly FileItineraryId $fileItineraryId,
        public readonly FileRoomAmountLogId $fileRoomAmountLogId,
        public readonly Value $value,
        public readonly Markup $markup,
        public readonly CreatedAt $createdAt,
        public readonly FileRoomAmountLog $fileRoomAmountLog
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
            'file_itinerary_id' => $this->fileItineraryId->value(),
            'file_room_amount_log_id' => $this->fileRoomAmountLogId->value(),
            'value' => $this->value->value(),
            'markup' => $this->markup->value() ,
            'created_at' => $this->createdAt->value(),
            'file_room_amount_log' => $this->fileRoomAmountLog->jsonSerialize(),
        ];
    }

}
