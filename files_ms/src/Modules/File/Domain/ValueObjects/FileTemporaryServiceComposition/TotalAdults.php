<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalAdults extends IntValueObject
{
    public function __construct(int $totalAdults)
    {
        parent::__construct($totalAdults);
    }
}
