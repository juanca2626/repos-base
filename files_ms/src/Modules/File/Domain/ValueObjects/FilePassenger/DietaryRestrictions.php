<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DietaryRestrictions extends StringOrNullableValueObject
{
    public function __construct(string|null $dietaryRestrictions)
    {
        parent::__construct($dietaryRestrictions);
    }
}
