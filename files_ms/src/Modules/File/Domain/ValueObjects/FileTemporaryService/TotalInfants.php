<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

class TotalInfants extends IntValueObject
{
    public function __construct(int $totalInfants)
    {
        parent::__construct($totalInfants);
    }
}
