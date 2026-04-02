<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class UseInvoice extends StringOrNullableValueObject
{
    public function __construct(string|null $useInvoice)
    {
        parent::__construct($useInvoice);
    }
}
