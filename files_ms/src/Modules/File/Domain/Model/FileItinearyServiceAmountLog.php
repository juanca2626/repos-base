<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog\FileServiceAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\FileItineraryServiceAmountLogId;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\Value;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\Markup;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\FileServiceAmountLog;
use Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog\CreatedAt;

use Src\Shared\Domain\Entity;

class FileItinearyServiceAmountLog extends Entity
{
    public function __construct(
        public readonly FileItineraryServiceAmountLogId $id,
        public readonly FileItineraryId $fileItineraryId,
        public readonly FileServiceAmountLogId $fileServiceAmountLogId,
        public readonly Value $value,
        public readonly Markup $markup,
        public readonly CreatedAt $createdAt,
        public readonly FileServiceAmountLog $fileServiceAmountLog
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
            'file_service_amount_log_id' => $this->fileServiceAmountLogId->value(),
            'value' => $this->value->value(),
            'markup' => $this->markup->value(),
            'created_at' => $this->createdAt->value(),
            'file_service_amount_log' => $this->fileServiceAmountLog->jsonSerialize(),

        ];
    }

}
