<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class CreatedAt extends DateValueObject
{
    public function __construct(string $dateIn)
    {
        parent::__construct($dateIn);
    }
}
