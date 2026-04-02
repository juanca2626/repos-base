<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class SuggestedAccommodationTpl extends IntOrNullValueObject
{
    public function __construct(int|null $suggestedAccommodationTpl)
    {
        parent::__construct($suggestedAccommodationTpl);
    }
}
