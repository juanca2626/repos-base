<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DoctypeIso extends StringOrNullableValueObject
{
    public function __construct(string|null $documentTypeId)
    {
        parent::__construct($documentTypeId);
    }
}
