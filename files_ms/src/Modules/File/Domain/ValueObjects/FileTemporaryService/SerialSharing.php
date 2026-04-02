<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class SerialSharing extends BooleanValueObject
{
    public function __construct(bool $serialSharing)
    {
        parent::__construct($serialSharing);
    }
}
