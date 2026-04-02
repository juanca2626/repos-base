<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Currency extends StringOrNullableValueObject
{

    public function __construct(string|null $currency)
    {
        parent::__construct($currency);
    }
}
