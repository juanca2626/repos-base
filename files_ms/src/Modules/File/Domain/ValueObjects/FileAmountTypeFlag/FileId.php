<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileId extends IntOrNullValueObject
{
    public function __construct(int|null $fileId)
    {
        parent::__construct($fileId);
    }
}
