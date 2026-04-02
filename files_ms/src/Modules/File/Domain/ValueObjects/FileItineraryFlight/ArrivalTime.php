<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ArrivalTime extends StringOrNullableValueObject
{
    public function __construct(string|null $arrivalTime)
    {
        parent::__construct($arrivalTime);
    }
}
