<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountTaxed extends FloatValueObject
{
    public function __construct(float $totalAmountTaxed = 0)
    {
        parent::__construct($totalAmountTaxed);
    }
}
