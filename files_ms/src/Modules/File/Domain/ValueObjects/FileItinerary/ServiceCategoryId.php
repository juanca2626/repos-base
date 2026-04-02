<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceCategoryId extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceCategoryId)
    {
        parent::__construct($serviceCategoryId);
    }
}
