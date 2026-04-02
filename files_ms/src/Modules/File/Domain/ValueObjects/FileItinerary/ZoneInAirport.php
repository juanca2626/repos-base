<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class ZoneInAirport extends BooleanValueObject
{
    public function __construct(float|null  $zoneInAirport)
    {
        parent::__construct($zoneInAirport);
    }
}
