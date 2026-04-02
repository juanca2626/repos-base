<?php

namespace Src\Modules\File\Domain\ValueObjects\FileCategory;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileId extends IntOrNullValueObject
{
    public function __construct(int|null $fileId)
    {
        parent::__construct($fileId);
    } 
}
