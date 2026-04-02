<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\TimeValueObject;

final class StartTime extends TimeValueObject
{
    public function __construct(string|null $startTime)
    {
        parent::__construct($startTime);
    }
}
