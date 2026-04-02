<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CityIso extends StringOrNullableValueObject
{
    public function __construct(string|null $cityIso)
    {
        parent::__construct($cityIso);
    }
}
