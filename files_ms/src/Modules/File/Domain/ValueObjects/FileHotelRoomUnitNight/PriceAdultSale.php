<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceAdultSale extends FloatValueObject
{
    public function __construct(float $priceAdultSale)
    {
        parent::__construct($priceAdultSale);
    }
}
