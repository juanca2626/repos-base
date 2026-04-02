<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileItineraryRoomAmountLogId extends IntOrNullValueObject
{
    public function __construct(int|null $fileItineraryRoomAmountLogId)
    {
        parent::__construct($fileItineraryRoomAmountLogId);
    }
}
