<?php

namespace Src\Modules\File\Domain\ValueObjects\Vip;

use Src\Shared\Domain\ValueObjects\StringValueObject;

class Name extends StringValueObject
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}
