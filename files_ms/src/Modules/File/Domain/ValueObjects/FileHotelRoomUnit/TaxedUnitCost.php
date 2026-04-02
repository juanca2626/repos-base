<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TaxedUnitCost extends FloatValueObject
{
    public function __construct(float $amountCost)
    {
        parent::__construct($amountCost);
    }
}
