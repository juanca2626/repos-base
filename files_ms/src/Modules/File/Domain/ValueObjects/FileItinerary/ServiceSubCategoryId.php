<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ServiceSubCategoryId extends StringOrNullableValueObject
{
    public function __construct(string|null $serviceSubCategoryId)
    {
        parent::__construct($serviceSubCategoryId);
    }
}
