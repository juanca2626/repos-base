<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class AirlineNumber extends StringOrNullableValueObject
{
    public function __construct(string|null $airlineNumber)
    {
        parent::__construct($airlineNumber);
    }
}
