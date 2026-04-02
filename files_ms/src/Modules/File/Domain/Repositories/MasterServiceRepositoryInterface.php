<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface MasterServiceRepositoryInterface
{ 
    public function searchAll(array $filters): LengthAwarePaginator;
}
