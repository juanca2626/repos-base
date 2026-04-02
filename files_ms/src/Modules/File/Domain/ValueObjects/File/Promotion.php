<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Promotion extends StringOrNullableValueObject
{

    public function __construct(string|null $promotion)
    {
        parent::__construct($promotion);
    }
}
