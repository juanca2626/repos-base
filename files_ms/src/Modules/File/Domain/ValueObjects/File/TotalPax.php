<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalPax extends IntValueObject
{
    public function __construct(int $totalPax)
    {
        parent::__construct($totalPax);
    }
}
