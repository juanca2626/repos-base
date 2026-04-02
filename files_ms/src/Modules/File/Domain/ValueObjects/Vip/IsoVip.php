<?php

namespace Src\Modules\File\Domain\ValueObjects\Vip;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

class IsoVip extends StringOrNullableValueObject
{
    public function __construct(string|null $isoVip)
    {
        parent::__construct($isoVip);
    }
}
