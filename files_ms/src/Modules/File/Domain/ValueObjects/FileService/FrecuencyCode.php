<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class FrecuencyCode extends StringOrNullableValueObject
{
    public function __construct(string|null $frecuencyCode)
    {
        parent::__construct($frecuencyCode);
    }
}
