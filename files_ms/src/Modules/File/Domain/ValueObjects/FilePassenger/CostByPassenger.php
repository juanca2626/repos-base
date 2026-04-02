<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Modules\File\Domain\Exceptions\InvalidDecimalValueException;
use Src\Shared\Domain\ValueObjects\FloatOrNullableValueObject;

final class CostByPassenger extends FloatOrNullableValueObject
{
    public function __construct(float|null  $costByPassenger)
    {
        parent::__construct($costByPassenger);
    }
}
