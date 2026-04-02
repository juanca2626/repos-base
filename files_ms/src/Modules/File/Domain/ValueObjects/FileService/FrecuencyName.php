<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class FrecuencyName extends StringOrNullableValueObject
{
    public function __construct(string|null $frecuencyName)
    {
        parent::__construct($frecuencyName);
    }
}
