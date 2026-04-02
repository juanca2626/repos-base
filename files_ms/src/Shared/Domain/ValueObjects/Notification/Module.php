<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Module extends StringValueObject
{
    public function __construct(string $module)
    {
        parent::__construct($module);
    }
}
