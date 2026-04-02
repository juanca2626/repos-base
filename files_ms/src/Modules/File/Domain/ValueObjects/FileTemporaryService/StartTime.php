<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Src\Shared\Domain\ValueObjects\TimeValueObject;

final class StartTime extends TimeValueObject
{
    public function __construct(string|null $startTime)
    {
        parent::__construct($startTime);
    }
}
