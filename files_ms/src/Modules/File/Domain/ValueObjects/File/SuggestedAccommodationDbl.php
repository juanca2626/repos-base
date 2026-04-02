<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class SuggestedAccommodationDbl extends IntOrNullValueObject
{
    public function __construct(int|null $suggestedAccommodationDbl)
    {
        parent::__construct($suggestedAccommodationDbl);
    }
}
