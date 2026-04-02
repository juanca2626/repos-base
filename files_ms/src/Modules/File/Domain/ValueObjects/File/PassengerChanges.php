<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class PassengerChanges extends IntOrNullValueObject
{
    public function __construct(int|null $PassengerChanges)
    {
        parent::__construct($PassengerChanges);
    }
}
