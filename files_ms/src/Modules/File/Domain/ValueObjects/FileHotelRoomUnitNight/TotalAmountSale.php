<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountSale extends FloatValueObject
{
    public function __construct(float $totalAmountSale)
    {
        parent::__construct($totalAmountSale);
    }
}
