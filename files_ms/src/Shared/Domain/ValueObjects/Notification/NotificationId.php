<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class NotificationId extends StringValueObject
{
    public function __construct(string $notification_id)
    {
        parent::__construct($notification_id);
    }
}
