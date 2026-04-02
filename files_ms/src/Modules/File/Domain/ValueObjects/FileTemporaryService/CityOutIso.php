<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CityOutIso extends StringOrNullableValueObject
{
    public function __construct(string|null $cityOutIso)
    {
        parent::__construct($cityOutIso);
    }
}
