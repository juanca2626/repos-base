<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Cc extends StringValueObject
{
    public readonly string $cc;

    public function __construct(string $cc)
    {
        parent::__construct($cc);
    }
}
