<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Motive extends StringOrNullableValueObject
{
    public function __construct(string|null $motive)
    {
        parent::__construct($motive);
    }
}
