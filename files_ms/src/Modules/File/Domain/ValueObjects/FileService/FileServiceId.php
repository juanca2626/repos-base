<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileServiceId extends IntOrNullValueObject
{
    public function __construct(int|null $fileServiceId)
    {
        parent::__construct($fileServiceId);
    }
}
