<?php

namespace Src\Modules\File\Domain\ValueObjects\FileAmountReason;

use Src\Shared\Domain\ValueObjects\BooleanOrNullValueObject;

final class InfluencesSale extends BooleanOrNullValueObject
{
    public function __construct(bool|null $influences_sale)
    {
        parent::__construct($influences_sale);
    }
}
