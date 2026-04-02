<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class CodeIFX extends StringValueObject
{
    public function __construct(string $code_ifx)
    {
        parent::__construct($code_ifx);
    }
}
