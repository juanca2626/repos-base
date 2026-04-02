<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class UseAccountingDocument extends BooleanValueObject
{
    public function __construct(bool $useAccountingDocument)
    {
        parent::__construct($useAccountingDocument);
    }
}
