<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountCreated extends FloatValueObject
{
    public function __construct(float $totalAmountCreated = 0)
    {
        parent::__construct($totalAmountCreated);
    }
}
