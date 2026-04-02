<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class StatusReasonId extends IntOrNullValueObject
{
    public function __construct(int|null $statusReasonId)
    {
        parent::__construct($statusReasonId);
    }
}
