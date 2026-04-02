<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\OperationServiceZero;

interface OperationServiceZeroRepositoryInterface
{
    public function filter(OperationServiceZero $service): mixed;
}