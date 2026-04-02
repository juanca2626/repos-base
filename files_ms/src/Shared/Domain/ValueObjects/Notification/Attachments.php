<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Attachments extends StringValueObject
{
    public readonly string $attachments;

    public function __construct(string $attachments)
    {
        parent::__construct($attachments);
    }
}
