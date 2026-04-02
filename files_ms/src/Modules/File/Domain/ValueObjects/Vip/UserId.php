<?php

namespace Src\Modules\File\Domain\ValueObjects\Vip;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class UserId extends IntOrNullValueObject
{
    public function __construct(int|null $userId)
    {
        parent::__construct($userId);
    }
}
