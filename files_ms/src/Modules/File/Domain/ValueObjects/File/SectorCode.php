<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class SectorCode extends StringOrNullableValueObject
{
    public function __construct(string|null $sectorCode)
    {
        parent::__construct($sectorCode);
    }
}
