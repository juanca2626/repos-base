<?php

namespace Src\Shared\Domain\ValueObjects\Language;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Name extends StringValueObject
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}
