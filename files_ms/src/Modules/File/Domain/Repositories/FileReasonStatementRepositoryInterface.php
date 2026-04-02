<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface FileReasonStatementRepositoryInterface
{
    public function search(array $filters): LengthAwarePaginator;
    public function searchAll(array $filters): array;
}