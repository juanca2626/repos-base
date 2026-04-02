<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface
{
 
    public function searchAll(array $filters): array;
}
