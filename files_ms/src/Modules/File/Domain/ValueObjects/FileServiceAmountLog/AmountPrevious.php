<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class AmountPrevious extends FloatValueObject
{
    public function __construct(float $amount_cost)
    {
        parent::__construct($amount_cost);
    }
}
