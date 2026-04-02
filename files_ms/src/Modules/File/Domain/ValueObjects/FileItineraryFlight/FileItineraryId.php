<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class FileItineraryId extends IntValueObject
{
    public function __construct(int $fileItineraryId)
    {
        parent::__construct($fileItineraryId);
    }
}
