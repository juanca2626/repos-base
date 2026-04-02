<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Modules\File\Domain\Exceptions\InvalidEntityException;
use Src\Shared\Domain\ValueObjects\EnumValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Phone extends StringOrNullableValueObject
{
    public function __construct(string|null $phone)
    {
        parent::__construct($phone);
    }
}
