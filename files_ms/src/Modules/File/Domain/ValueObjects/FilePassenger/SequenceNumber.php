<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class SequenceNumber extends IntOrNullValueObject
{
    public function __construct(int|null $sequenceNumber)
    {
        parent::__construct($sequenceNumber);
    }
}
