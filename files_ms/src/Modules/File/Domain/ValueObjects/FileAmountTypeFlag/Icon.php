<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Icon extends StringOrNullableValueObject
{
    public function __construct(string|null $icon)
    {
        parent::__construct($icon);
    }
}
