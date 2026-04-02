<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\IntValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class BranchNumber extends StringOrNullableValueObject
{
    public function __construct(string|null $branchNumber)
    {
        parent::__construct($branchNumber);
    }
}
