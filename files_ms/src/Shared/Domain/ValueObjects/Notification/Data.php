<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Data extends StringValueObject
{
    public readonly string $data;

    public function __construct(string $data)
    {
        parent::__construct($data);
    }
}
