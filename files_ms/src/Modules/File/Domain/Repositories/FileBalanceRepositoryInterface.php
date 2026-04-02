<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface FileBalanceRepositoryInterface{
    public function index(array $filter): LengthAwarePaginator;
}
