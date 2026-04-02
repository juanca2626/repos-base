<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceExtraCost extends FloatValueObject
{
    public function __construct(float $priceExtraCost)
    {
        parent::__construct($priceExtraCost);
    }
}
