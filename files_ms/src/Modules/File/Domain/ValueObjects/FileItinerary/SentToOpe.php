<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class SentToOpe extends BooleanValueObject
{
    public function __construct(bool $isInOpe)
    {
        parent::__construct($isInOpe);
    }
}
