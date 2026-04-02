<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\InvalidDecimalValueException;
use Src\Shared\Domain\ValueObjects\FloatOrNullableValueObject;

final class StelaProcessing extends FloatOrNullableValueObject
{
    public function __construct(float|null $stelaProcessing = 0)
    {
        parent::__construct($stelaProcessing);
    }
}
