<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class FileNumber extends IntValueObject
{
    public function __construct(int $fileNumber)
    {
        parent::__construct($fileNumber);
    }
}
