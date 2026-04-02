<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\ServiceZero;

interface ServiceZeroRepositoryInterface
{
    public function save(ServiceZero $service): mixed;
}