<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CityInName extends StringOrNullableValueObject
{
    public function __construct(string|null $cityInName)
    {
        parent::__construct($cityInName);
    }
}
