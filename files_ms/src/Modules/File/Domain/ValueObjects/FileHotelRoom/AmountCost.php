<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class AmountCost extends FloatValueObject
{
    public function __construct(float $amountCost = 0)
    {
        parent::__construct($amountCost);
    }
}
