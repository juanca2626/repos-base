<?php

namespace Src\Shared\Domain\ValueObjects\Country;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Code extends StringValueObject
{
    public function __construct(string $code)
    {
        parent::__construct($code);
    }
}
