<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ZoneOutIso extends StringOrNullableValueObject
{
    public function __construct(string|null $outZone)
    {
        parent::__construct($outZone);
    }
}
