<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileTemporaryServiceId extends IntOrNullValueObject
{
    public function __construct(int|null $fileTemporaryServiceId)
    {
        parent::__construct($fileTemporaryServiceId);
    }
}
