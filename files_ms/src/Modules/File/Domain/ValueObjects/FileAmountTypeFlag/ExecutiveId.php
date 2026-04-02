<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class ExecutiveId extends IntOrNullValueObject
{
    public function __construct(int|null $executiveId)
    {
        parent::__construct($executiveId);
    }
}
