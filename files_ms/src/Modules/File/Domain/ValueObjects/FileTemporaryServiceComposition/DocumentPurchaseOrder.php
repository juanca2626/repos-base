<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class DocumentPurchaseOrder extends BooleanValueObject
{
    public function __construct(bool $documentPurchaseOrder)
    {
        parent::__construct($documentPurchaseOrder);
    }
}
