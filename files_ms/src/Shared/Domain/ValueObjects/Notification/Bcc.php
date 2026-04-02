<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Bcc extends StringValueObject
{
    public readonly string $bcc;

    public function __construct(string $bcc)
    {
        parent::__construct($bcc);
    }
}
