<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class SendCommunication extends StringValueObject 
{
    public function __construct(string $sendCommunication)
    {
        parent::__construct($sendCommunication);
    }
}
