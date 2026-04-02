<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\RatesServiceZero;

interface RatesServiceZeroRepositoryInterface
{
    public function filter(RatesServiceZero $service): mixed;
}