<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DepartureTime extends StringOrNullableValueObject
{
    public function __construct(string|null $departureTime)
    {
        parent::__construct($departureTime);
    }
}
