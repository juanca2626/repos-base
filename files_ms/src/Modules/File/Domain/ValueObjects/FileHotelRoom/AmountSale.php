<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class AmountSale extends FloatValueObject
{
    public function __construct(float $amountSale = 0)
    {
        parent::__construct($amountSale);
    }
}
