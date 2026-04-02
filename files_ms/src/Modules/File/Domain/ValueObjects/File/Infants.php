<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Infants extends IntValueObject
{
    public function __construct(int $infants)
    {
        parent::__construct($infants);
    }
}
