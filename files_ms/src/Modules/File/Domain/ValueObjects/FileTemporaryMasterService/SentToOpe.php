<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class SentToOpe extends BooleanValueObject
{
    public function __construct(bool $sentToOpe)
    {
        parent::__construct($sentToOpe);
    }
}
