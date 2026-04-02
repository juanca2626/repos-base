<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ExecutiveCodeSale extends StringOrNullableValueObject
{

    public function __construct(string|null $executiveCodeSale)
    {
        parent::__construct($executiveCodeSale);
    }
}
