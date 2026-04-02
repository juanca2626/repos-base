<?php

namespace Src\Modules\File\Domain\ValueObjects\StatusReason;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Visible extends StringOrNullableValueObject
{
    public function __construct(string|null $visible)
    {
        parent::__construct($visible);
    }
}
