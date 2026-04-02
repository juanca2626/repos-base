<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ExecutiveCode extends StringOrNullableValueObject
{
    public function __construct(string|null $executiveCode)
    {
        parent::__construct($executiveCode);
    }
}
