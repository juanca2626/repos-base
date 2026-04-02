<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\BooleanOrNullValueObject;

final class GenerateStatement extends BooleanOrNullValueObject
{
    public function __construct(bool|null $generateStatement)
    {
        parent::__construct($generateStatement);
    }
}
