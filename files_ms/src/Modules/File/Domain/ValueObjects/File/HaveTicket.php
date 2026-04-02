<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class HaveTicket extends BooleanValueObject
{
    public function __construct(bool $haveTicket)
    {
        parent::__construct($haveTicket);
    }
}
