<?php

namespace Src\Modules\File\Domain\ValueObjects\FileStatusReason;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class CreatedAt extends DateValueObject
{
    public function __construct(string|null $createdAt)
    {
        parent::__construct($createdAt);
    }
}
