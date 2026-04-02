<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class ZoneInId extends IntOrNullValueObject
{
    public function __construct(int|null $zoneInId)
    {
        parent::__construct($zoneInId);
    }
}
