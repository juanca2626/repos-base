<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
 
    public function searchAll(array $filters): array;
}
