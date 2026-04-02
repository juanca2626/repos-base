<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\TimeValueObject;

final class DepartureTime extends TimeValueObject
{
    public function __construct(string|null $departureTime)
    {
        parent::__construct($departureTime);
    }
}
