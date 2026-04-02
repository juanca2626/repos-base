<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileItineraryServiceAmountLogId extends IntOrNullValueObject
{
    public function __construct(int|null $fileItineraryServiceAmountLogId)
    {
        parent::__construct($fileItineraryServiceAmountLogId);
    }
}
