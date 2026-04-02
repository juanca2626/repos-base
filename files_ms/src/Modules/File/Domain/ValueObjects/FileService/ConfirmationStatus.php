<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class ConfirmationStatus extends BooleanValueObject
{
    public function __construct(bool $confirmationStatus)
    {
        parent::__construct($confirmationStatus);
    }
}
