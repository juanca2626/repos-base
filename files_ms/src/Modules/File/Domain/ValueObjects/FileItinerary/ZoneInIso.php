<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ZoneInIso extends StringOrNullableValueObject
{
    public function __construct(string|null $startZone)
    {
        parent::__construct($startZone);
    }
}
