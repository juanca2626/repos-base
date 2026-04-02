<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface FileAmountTypeFlagRepositoryInterface
{
    public function search(array $filters): LengthAwarePaginator;
    public function all(): array;
    public function locked(): array;
}
