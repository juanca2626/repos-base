<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CountryInIso extends StringOrNullableValueObject
{
    public function __construct(string|null $countryInIso)
    {
        parent::__construct($countryInIso);
    }
}
