<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Notes extends StringOrNullableValueObject
{
    public function __construct(string|null $notes)
    {
        parent::__construct($notes);
    }
}
