<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceUnit;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileServiceUnitId extends IntOrNullValueObject
{
    public function __construct(int|null $fileServiceUnitId)
    {
        parent::__construct($fileServiceUnitId);
    }
}
