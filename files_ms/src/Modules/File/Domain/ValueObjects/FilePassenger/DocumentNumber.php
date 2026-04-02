<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DocumentNumber extends StringOrNullableValueObject
{
    public function __construct(string|null $documentNumber)
    {
        parent::__construct($documentNumber);
    }
}
