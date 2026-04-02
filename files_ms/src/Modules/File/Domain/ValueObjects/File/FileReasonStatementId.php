<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class FileReasonStatementId extends IntOrNullValueObject
{
    public function __construct(int|null $fileReasonStatementId)
    {
        parent::__construct($fileReasonStatementId);
    }
}
