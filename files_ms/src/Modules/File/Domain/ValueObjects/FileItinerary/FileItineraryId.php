<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileItineraryId extends IntOrNullValueObject
{
    public function __construct(int|null $fileItineraryId)
    {
        parent::__construct($fileItineraryId);
    }
}
