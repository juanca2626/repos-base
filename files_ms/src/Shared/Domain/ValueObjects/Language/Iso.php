<?php

namespace Src\Shared\Domain\ValueObjects\Language;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Iso extends StringValueObject
{
    public function __construct(string $iso)
    {
        parent::__construct($iso);
    }
}
