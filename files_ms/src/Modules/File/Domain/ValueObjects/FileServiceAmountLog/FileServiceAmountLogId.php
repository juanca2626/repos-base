<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileServiceAmountLogId extends IntOrNullValueObject
{
    public function __construct(int|null $fileServiceAmountLogId)
    {
        parent::__construct($fileServiceAmountLogId);
    }
}
