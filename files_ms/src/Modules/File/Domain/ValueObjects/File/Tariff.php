<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Tariff extends StringOrNullableValueObject
{
    public function __construct(string|null $tariff)
    {
        parent::__construct($tariff);
    }
}
