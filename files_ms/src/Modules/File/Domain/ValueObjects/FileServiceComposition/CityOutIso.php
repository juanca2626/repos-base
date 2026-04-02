<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CityOutIso extends StringOrNullableValueObject 
{
    public function __construct(string|null $city_out_iso)
    {
        parent::__construct($city_out_iso);
    }
}
