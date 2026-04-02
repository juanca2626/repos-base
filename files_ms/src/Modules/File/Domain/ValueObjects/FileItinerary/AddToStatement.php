<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class AddToStatement extends BooleanValueObject
{
    public function __construct(float|null  $addToStatement)
    {
        parent::__construct($addToStatement);
    }
}
