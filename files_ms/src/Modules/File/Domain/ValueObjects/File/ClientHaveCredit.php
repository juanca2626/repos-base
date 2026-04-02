<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ClientHaveCredit extends IntValueObject
{
    public function __construct(int $clientHaveCredit)
    {
        parent::__construct($clientHaveCredit);
    }
}
