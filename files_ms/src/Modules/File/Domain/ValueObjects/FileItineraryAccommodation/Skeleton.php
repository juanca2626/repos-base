<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Skeleton extends StringValueObject
{
    public function __construct(string $skeleton)
    {
        parent::__construct($skeleton);
    }
}
