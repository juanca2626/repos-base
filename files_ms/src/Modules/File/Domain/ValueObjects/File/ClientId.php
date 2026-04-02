<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ClientId extends IntValueObject
{
    public function __construct(int $clientId)
    {
        parent::__construct($clientId);
    }
}
