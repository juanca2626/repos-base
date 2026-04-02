<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class To extends StringValueObject
{
    public readonly string $to;

    public function __construct(string $to)
    {
        parent::__construct($to);
    }
}
