<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CountryIso extends StringOrNullableValueObject
{
    public function __construct(string|null $countryIso)
    {
        parent::__construct($countryIso);
    }
}
