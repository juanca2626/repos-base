<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\DetailServiceZero;

interface DetailServiceZeroRepositoryInterface
{
    public function filter(DetailServiceZero $service): mixed;
}