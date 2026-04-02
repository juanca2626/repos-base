<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject; 

class ServiceRateId extends IntOrNullValueObject
{
    public function __construct(int|null $totalAdults)
    {
        parent::__construct($totalAdults);
    }
}
