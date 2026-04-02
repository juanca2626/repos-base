<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class UseTicket extends BooleanValueObject
{
    public function __construct(bool $useTicket)
    {
        parent::__construct($useTicket);
    }
}
