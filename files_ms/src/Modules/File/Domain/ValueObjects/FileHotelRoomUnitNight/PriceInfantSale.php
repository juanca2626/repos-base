<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class PriceInfantSale extends FloatValueObject
{
    public function __construct(float $priceInfantSale)
    {
        parent::__construct($priceInfantSale);
    }
}
