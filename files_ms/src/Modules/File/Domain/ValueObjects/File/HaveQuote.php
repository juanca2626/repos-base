<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class HaveQuote extends BooleanValueObject
{
    public function __construct(bool $haveQuote)
    {
        parent::__construct($haveQuote);
    }
}
