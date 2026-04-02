<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class FilesMsParameters extends StringOrNullableValueObject
{
    public function __construct(string|null $filesMsParameters)
    {
        parent::__construct($filesMsParameters);
    }
}
