<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Description extends StringValueObject
{
    public function __construct(string $description)
    {
        parent::__construct($description);
    }
}
