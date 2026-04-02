<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CountryInName extends StringOrNullableValueObject
{
    public function __construct(string|null $countryInName)
    {
        parent::__construct($countryInName);
    }
}
