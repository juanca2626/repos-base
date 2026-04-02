<?php

namespace Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class Amount extends FloatValueObject
{
    public function __construct(float $amount_cost)
    {
        parent::__construct($amount_cost);
    }
}
