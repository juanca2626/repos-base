<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class RatePlanCode extends StringOrNullableValueObject
{
    public function __construct(string|null $ratePlanCode)
    {
        parent::__construct($ratePlanCode);
    }
}
