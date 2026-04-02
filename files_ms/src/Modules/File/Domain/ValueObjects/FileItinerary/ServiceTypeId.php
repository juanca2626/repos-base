<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceTypeId extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceTypeId)
    {
        parent::__construct($serviceTypeId);
    }
}
