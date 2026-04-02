<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceCode extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceCode)
    {
        parent::__construct($serviceCode);
    }
}
