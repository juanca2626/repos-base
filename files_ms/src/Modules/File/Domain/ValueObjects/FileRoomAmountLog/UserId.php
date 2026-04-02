<?php

namespace Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class UserId extends IntValueObject
{
    public function __construct(string $userId)
    {
        parent::__construct($userId);
    }
}
