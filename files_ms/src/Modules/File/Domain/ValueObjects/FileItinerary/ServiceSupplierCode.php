<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceSupplierCode extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceMaskSupplierCode)
    {
        parent::__construct($serviceMaskSupplierCode);
    }
}
