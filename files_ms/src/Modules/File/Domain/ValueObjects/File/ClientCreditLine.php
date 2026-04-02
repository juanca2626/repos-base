<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class ClientCreditLine extends FloatValueObject
{
    public function __construct(float $clientCreditLine)
    {
        parent::__construct($clientCreditLine);
    }
}
