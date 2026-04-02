<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class StatusReason extends StringOrNullableValueObject
{
    public function __construct(string|null $statusReason)
    {
        parent::__construct($statusReason);
    }
}
