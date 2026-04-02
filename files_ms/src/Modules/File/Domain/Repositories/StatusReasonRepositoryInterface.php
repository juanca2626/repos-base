<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface StatusReasonRepositoryInterface
{ 
    public function searchAll(array $filters): array;
}
