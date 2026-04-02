<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Observation extends StringOrNullableValueObject
{
    public function __construct(string|null $observation)
    {
        parent::__construct($observation);
    }
}
