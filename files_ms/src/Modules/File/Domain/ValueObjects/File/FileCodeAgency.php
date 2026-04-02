<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class FileCodeAgency extends StringOrNullableValueObject
{
    public function __construct(string|null $fileCodeAgency)
    {
        parent::__construct($fileCodeAgency);
    }
}
