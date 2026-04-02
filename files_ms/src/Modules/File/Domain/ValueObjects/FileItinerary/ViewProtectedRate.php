<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class ViewProtectedRate extends BooleanValueObject
{
    public function __construct(float|null  $viewProtectedRate)
    {
        parent::__construct($viewProtectedRate);
    }
}
