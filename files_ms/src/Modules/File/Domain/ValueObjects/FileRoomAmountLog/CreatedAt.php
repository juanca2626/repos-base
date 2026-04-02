<?php

namespace Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class CreatedAt extends DateValueObject
{
    public function __construct(string|null $dateIn)
    {
        parent::__construct($dateIn);
    }
}
