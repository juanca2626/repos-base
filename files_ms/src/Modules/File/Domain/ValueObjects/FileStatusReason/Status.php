<?php

namespace Src\Modules\File\Domain\ValueObjects\FileStatusReason;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Status extends StringOrNullableValueObject
{
    public function __construct(string|null $status)
    {
        parent::__construct($status);
    }
}
