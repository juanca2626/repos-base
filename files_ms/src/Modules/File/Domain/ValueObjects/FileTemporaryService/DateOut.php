<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Src\Shared\Domain\ValueObjects\DateValueObject;

final class DateOut extends DateValueObject
{
    public function __construct(string $dateOut)
    {
        parent::__construct($dateOut);
    }
}
