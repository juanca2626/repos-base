<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class SuggestedAccommodationSgl extends IntOrNullValueObject
{
    public function __construct(int|null $suggestedAccommodationSgl)
    {
        parent::__construct($suggestedAccommodationSgl);
    }
}
