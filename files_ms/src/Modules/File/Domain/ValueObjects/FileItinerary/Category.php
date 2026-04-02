<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Category extends StringOrNullableValueObject
{
    public function __construct(string|null $category)
    {
        parent::__construct($category);
    }
}
