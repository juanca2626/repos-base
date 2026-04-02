<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ExecutiveCodeProcess extends StringOrNullableValueObject
{
    public function __construct(string|null $executiveCodeProcess)
    {
        parent::__construct($executiveCodeProcess);
    }
}
