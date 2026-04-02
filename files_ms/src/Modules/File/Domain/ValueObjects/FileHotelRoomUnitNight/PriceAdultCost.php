<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceAdultCost extends FloatValueObject
{
    public function __construct(float $priceAdultCost)
    {
        parent::__construct($priceAdultCost);
    }
}
