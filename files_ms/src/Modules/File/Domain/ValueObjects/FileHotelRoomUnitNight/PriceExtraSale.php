<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceExtraSale extends FloatValueObject
{
    public function __construct(float $priceExtraSale)
    {
        parent::__construct($priceExtraSale);
    }
}
