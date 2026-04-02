<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Src\Shared\Domain\ValueObjects\DateValueObject;

final class DateIn extends DateValueObject
{
    public function __construct(string $dateIn)
    {
        parent::__construct($dateIn);
    }
}
