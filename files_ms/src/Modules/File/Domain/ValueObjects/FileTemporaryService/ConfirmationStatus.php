<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class ConfirmationStatus extends BooleanValueObject
{
    public function __construct(float|null  $confirmationStatus)
    {
        parent::__construct($confirmationStatus);
    }
}
