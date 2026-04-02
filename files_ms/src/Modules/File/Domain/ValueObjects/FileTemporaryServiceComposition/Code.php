<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Code extends StringOrNullableValueObject
{
    public function __construct(string|null $code)
    {
        parent::__construct($code);
    }
}
