<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountReason;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Process extends StringOrNullableValueObject
{
    public function __construct(string|null $process)
    {
        parent::__construct($process);
    }
}
