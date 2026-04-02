<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class AirlineCode extends StringOrNullableValueObject
{
    public function __construct(string|null $airlineCode)
    {
        parent::__construct($airlineCode);
    }

}
