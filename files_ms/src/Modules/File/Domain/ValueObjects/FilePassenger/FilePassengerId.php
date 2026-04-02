<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class FilePassengerId extends IntValueObject
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }
}
