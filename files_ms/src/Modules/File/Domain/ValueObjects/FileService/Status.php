<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class Status extends BooleanValueObject
{
    public function __construct(bool $status)
    {
        parent::__construct($status);
    }
}
