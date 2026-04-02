<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountCost extends FloatValueObject
{
    public function __construct(float $totalAmountCost)
    {
        parent::__construct($totalAmountCost);
    }
}
