<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class MessageId extends StringValueObject
{
    public function __construct(string $message_id)
    {
        parent::__construct($message_id);
    }
}
