<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class Taxes extends FloatValueObject
{
    public function __construct(float $taxes = 0)
    {
        parent::__construct($taxes);
    }
}
