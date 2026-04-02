<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceChildSale extends FloatValueObject
{
    public function __construct(float $priceChildSale)
    {
        parent::__construct($priceChildSale);
    }
}
