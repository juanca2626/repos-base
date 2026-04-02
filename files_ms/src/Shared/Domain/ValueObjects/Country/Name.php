<?php

namespace Src\Shared\Domain\ValueObjects\Country;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Name extends StringValueObject
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}
