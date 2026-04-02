<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\DTO\FileWithRelationsData;
use Src\Modules\File\Domain\Model\File;


interface FileCategoryRepositoryInterface
{
    public function create(int $fileId, array $params): bool;     
}
