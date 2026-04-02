<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceSummary extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceSummary)
    {
        parent::__construct($serviceSummary);
    }
}
