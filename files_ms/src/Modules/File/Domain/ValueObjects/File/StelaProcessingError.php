<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class StelaProcessingError extends StringOrNullableValueObject
{
    public function __construct(string|null $stelaProcessingError)
    {
        parent::__construct($stelaProcessingError);
    }
}
