<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CityOutName extends StringOrNullableValueObject
{
    public function __construct(string|null $cityName)
    {
        parent::__construct($cityName);
    }
}
