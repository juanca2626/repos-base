<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Frequent extends StringOrNullableValueObject
{
    public function __construct(string|null $frequent)
    {
        parent::__construct($frequent);
    }
}
