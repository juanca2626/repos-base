<?php

namespace Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileRoomAmountLogId extends IntOrNullValueObject
{
    public function __construct(int|null $fileRoomAmountLogId)
    {
        parent::__construct($fileRoomAmountLogId);
    }
}
