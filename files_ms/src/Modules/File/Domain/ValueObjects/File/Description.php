<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Description extends StringOrNullableValueObject
{
    public function __construct(string|null $description)
    {
        parent::__construct($description);
    }
}
