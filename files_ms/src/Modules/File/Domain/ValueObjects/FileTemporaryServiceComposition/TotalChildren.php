<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalChildren extends IntValueObject
{
    public function __construct(int $totalChildren)
    {
        parent::__construct($totalChildren);
    }
}
