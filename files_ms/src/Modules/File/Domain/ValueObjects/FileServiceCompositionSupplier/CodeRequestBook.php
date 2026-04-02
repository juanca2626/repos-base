<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CodeRequestBook extends StringOrNullableValueObject 
{
    public function __construct(string|null $codeRequestBook)
    {
        parent::__construct($codeRequestBook);
    }
}
