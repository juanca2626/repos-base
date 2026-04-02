<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class AccountingDocumentSent extends IntOrNullValueObject
{
    public function __construct(int|null $accountingDocumentSent)
    {
        parent::__construct($accountingDocumentSent);
    }
}
