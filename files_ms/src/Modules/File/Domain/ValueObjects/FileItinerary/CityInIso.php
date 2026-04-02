<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CityInIso extends StringOrNullableValueObject
{

    public function __construct(string|null $cityInIso)
    {
        parent::__construct($cityInIso);
    }

}
