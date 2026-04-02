<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class Status extends BooleanValueObject
{
    public function __construct(float|null  $status)
    {
        parent::__construct($status);
    }
}
