<?php

namespace Src\Shared\Domain\ValueObjects\Notification;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class ObjectId extends StringValueObject
{
    public function __construct(string $object_id)
    {
        parent::__construct($object_id);
    }
}
