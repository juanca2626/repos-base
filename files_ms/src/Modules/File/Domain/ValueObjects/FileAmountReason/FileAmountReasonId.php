<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountReason;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileAmountReasonId extends IntOrNullValueObject
{
    public function __construct(int|null $id)
    {
        parent::__construct($id);
    }
}
