<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class ItemNumber extends IntOrNullValueObject
{
    public function __construct(int|null $itemNumber)
    {
        parent::__construct($itemNumber);
    }
}
