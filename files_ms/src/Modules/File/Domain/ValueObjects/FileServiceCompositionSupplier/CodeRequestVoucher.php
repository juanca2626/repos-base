<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CodeRequestVoucher extends StringOrNullableValueObject 
{
    public function __construct(string|null $codeRequestVoucher)
    {
        parent::__construct($codeRequestVoucher);
    }
}
