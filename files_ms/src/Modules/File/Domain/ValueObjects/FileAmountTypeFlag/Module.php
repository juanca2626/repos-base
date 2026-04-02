<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Module extends StringOrNullableValueObject
{
    public function __construct(string|null $module)
    {
        parent::__construct($module);
    }
}
