<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Name extends StringOrNullableValueObject
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}
