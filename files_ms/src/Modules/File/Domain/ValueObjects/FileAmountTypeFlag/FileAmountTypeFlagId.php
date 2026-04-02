<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class FileAmountTypeFlagId extends IntValueObject
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }
}
