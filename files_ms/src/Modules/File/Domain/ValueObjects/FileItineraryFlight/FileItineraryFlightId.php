<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileItineraryFlightId extends IntOrNullValueObject
{
    public function __construct(int|null $fileItineraryFlightId)
    {
        parent::__construct($fileItineraryFlightId);
    }
}
