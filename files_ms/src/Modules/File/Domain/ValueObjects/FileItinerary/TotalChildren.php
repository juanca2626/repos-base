<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

class TotalChildren extends IntValueObject
{
    public function __construct(int $totalChildren)
    {
        parent::__construct($totalChildren);
    }
}
