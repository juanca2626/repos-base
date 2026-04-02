<?php

namespace Src\Modules\File\Domain\ValueObjects\StatusReason;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class StatusIso extends StringOrNullableValueObject
{
    public function __construct(string|null $statusIso)
    {
        parent::__construct($statusIso);
    }
}
