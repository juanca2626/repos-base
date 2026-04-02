<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FilePassengerId extends IntOrNullValueObject
{
    public function __construct(int|null $filePassengerId)
    {
        parent::__construct($filePassengerId);
    }
}
