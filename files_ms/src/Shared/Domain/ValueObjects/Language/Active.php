<?php

namespace Src\Shared\Domain\ValueObjects\Language;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Active extends IntValueObject
{
    public function __construct(int $active)
    {
        parent::__construct($active);
    }
}
