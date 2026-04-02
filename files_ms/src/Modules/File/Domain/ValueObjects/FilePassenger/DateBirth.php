<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\DateValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DateBirth extends DateValueObject
{
    public function __construct(string|null $dateBirth)
    {
        parent::__construct($dateBirth);
    }
}
