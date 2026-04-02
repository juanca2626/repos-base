<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryRoomAmountLog;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class Value extends FloatValueObject
{
    public function __construct(float $amount_cost)
    {
        parent::__construct($amount_cost);
    }
}
