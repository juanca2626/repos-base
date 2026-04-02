<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Currency extends StringValueObject
{
    public function __construct(string $currency)
    {
        parent::__construct($currency);
    }
}
