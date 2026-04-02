<?php

namespace Src\Modules\File\Domain\ValueObjects\FileStatusReason;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class StatusReasonId extends IntOrNullValueObject
{
    public function __construct(int|null $statusReasonId)
    {
        parent::__construct($statusReasonId);
    }
}
