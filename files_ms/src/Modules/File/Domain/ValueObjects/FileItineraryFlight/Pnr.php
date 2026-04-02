<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Pnr extends StringOrNullableValueObject
{
    public function __construct(string|null $pnr)
    {
        parent::__construct($pnr);
    }

}
