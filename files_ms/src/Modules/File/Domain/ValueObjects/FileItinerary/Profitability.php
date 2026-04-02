<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Modules\File\Domain\Exceptions\InvalidDecimalValueException;
use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class Profitability extends FloatValueObject
{
    public function __construct(float|null $profitability)
    {
        parent::__construct($profitability);
    }
}
