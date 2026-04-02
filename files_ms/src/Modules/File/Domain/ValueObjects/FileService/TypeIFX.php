<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class TypeIFX extends StringValueObject
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}
