<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TaxedSale extends FloatValueObject
{
    public function __construct(float $taxedUnitSale = 0)
    {
        parent::__construct($taxedUnitSale);
    }
}
