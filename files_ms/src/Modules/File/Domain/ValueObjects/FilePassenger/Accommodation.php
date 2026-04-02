<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Accommodation extends StringOrNullableValueObject
{
    public function __construct(string|null $accommodation)
    {
        parent::__construct($accommodation);
    }
}
