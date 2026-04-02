<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TaxedCost extends FloatValueObject
{
    public function __construct(float $taxedUnitCost = 0)
    {
        parent::__construct($taxedUnitCost);
    }
}
