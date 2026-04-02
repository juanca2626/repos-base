<?php

namespace Src\Modules\File\Domain\ValueObjects\FileStatusReason;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileId extends IntOrNullValueObject
{
    public function __construct(int|null $fileId)
    {
        parent::__construct($fileId);
    } 
}
