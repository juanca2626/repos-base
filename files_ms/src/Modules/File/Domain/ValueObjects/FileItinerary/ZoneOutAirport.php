<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class ZoneOutAirport extends BooleanValueObject
{
    public function __construct(float|null  $zoneOutAirport)
    {
        parent::__construct($zoneOutAirport);
    }
}
