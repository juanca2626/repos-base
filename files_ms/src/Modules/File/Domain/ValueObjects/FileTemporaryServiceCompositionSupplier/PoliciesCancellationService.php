<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class PoliciesCancellationService extends StringOrNullableValueObject 
{
    public function __construct(string|null $policiesCancellationService)
    {
        parent::__construct($policiesCancellationService);
    }
}
