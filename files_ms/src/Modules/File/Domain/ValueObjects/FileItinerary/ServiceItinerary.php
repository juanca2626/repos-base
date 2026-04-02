<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceItinerary extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceItinerary)
    {
        parent::__construct($serviceItinerary);
    }
}
