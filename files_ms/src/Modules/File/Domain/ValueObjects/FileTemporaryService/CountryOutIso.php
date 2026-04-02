<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CountryOutIso extends StringOrNullableValueObject
{
    public function __construct(string|null $outCountryIso)
    {
        parent::__construct($outCountryIso);
    }
}
