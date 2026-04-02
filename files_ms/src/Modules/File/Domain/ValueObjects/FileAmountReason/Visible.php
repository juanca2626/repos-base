<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountReason;

use Src\Shared\Domain\ValueObjects\BooleanOrNullValueObject;

final class Visible extends BooleanOrNullValueObject
{
    public function __construct(bool|null $visible)
    {
        parent::__construct($visible);
    }
}
