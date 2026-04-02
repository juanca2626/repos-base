<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class TotalAmountInvoice extends FloatValueObject
{
    public function __construct(float $totalAmountInvoice = 0)
    {
        parent::__construct($totalAmountInvoice);
    }
}
