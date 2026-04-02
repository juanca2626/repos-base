<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class DateIn extends DateValueObject
{
    public function __construct(string $dateIn)
    {
        parent::__construct($dateIn);
    }
}
