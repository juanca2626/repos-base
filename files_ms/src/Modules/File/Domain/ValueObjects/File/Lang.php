<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Lang extends StringOrNullableValueObject
{
    public function __construct(string|null $lang)
    {
        parent::__construct($lang);
    }
}
