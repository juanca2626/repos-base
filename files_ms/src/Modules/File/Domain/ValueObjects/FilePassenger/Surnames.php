<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Surnames extends StringOrNullableValueObject
{
    public function __construct(string|null $surnames)
    {
        parent::__construct($surnames);
    }
}
