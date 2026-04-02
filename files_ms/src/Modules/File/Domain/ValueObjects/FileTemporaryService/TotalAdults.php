<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

class TotalAdults extends IntValueObject
{
    public function __construct(int $totalAdults)
    {
        parent::__construct($totalAdults);
    }
}
