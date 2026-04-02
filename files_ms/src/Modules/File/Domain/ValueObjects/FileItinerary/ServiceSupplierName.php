<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceSupplierName extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceMaskSupplierName)
    {
        parent::__construct($serviceMaskSupplierName);
    }
}
