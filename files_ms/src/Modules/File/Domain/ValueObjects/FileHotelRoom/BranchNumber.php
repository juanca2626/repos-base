<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

final class BranchNumber extends IntOrNullValueObject
{
    public function __construct(int|null $branchNumber)
    {
        parent::__construct($branchNumber);
    }
}
