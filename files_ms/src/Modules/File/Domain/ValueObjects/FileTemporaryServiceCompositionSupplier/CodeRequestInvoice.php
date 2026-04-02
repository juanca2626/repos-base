<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CodeRequestInvoice extends StringOrNullableValueObject 
{
    public function __construct(string|null $codeRequestInvoice)
    {
        parent::__construct($codeRequestInvoice);
    }
}
