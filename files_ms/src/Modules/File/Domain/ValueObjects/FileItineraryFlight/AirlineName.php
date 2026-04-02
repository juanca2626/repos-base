<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class AirlineName extends StringOrNullableValueObject
{

    public function __construct(string|null $airlineName)
    {
        parent::__construct($airlineName);
    }

}
