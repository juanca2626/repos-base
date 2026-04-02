<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class ZoneOutId extends IntOrNullValueObject
{
    public function __construct(int|null $zoneOutId)
    {
        parent::__construct($zoneOutId);
    }
}
