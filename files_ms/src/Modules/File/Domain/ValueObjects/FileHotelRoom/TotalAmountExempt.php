<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountExempt extends FloatValueObject
{
    public function __construct(float $totalAmountExempt = 0)
    {
        parent::__construct($totalAmountExempt);
    }
}
