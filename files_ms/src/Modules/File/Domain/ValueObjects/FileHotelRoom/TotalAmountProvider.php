<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountProvider extends FloatValueObject
{
    public function __construct(float $totalAmountProvider = 0)
    {
        parent::__construct($totalAmountProvider);
    }
}
