<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Name extends StringOrNullableValueObject
{
    public function __construct(string|null $name)
    {
        parent::__construct($name);
    }
}
