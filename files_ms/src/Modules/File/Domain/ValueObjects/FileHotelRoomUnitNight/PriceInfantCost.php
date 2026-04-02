<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceInfantCost extends FloatValueObject
{
    public function __construct(float $priceInfantCost)
    {
        parent::__construct($priceInfantCost);
    }
}
