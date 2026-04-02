<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CountryOutName extends StringOrNullableValueObject
{
    public function __construct(string|null $countryOutName)
    {
        parent::__construct($countryOutName);
    }
}
