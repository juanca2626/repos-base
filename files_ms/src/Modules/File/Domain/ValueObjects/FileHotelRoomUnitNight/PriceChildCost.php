<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceChildCost extends FloatValueObject
{
    public function __construct(float $priceChildCost)
    {
        parent::__construct($priceChildCost);
    }
}
