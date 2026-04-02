<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class HaveInvoice extends BooleanValueObject
{
    public function __construct(bool $haveInvoice)
    {
        parent::__construct($haveInvoice);
    }
}
