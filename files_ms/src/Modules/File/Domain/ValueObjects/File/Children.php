<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Children extends IntValueObject
{
    public function __construct(int $children)
    {
        parent::__construct($children);
    }
}
