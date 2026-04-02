<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Submodule extends StringValueObject
{
    public function __construct(string $submodule)
    {
        parent::__construct($submodule);
    }
}
