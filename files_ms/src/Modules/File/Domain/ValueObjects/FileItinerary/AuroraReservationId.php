<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class AuroraReservationId extends IntOrNullValueObject
{
    public function __construct(int|null $auroraReservationId)
    {
        parent::__construct($auroraReservationId);
    }
}
