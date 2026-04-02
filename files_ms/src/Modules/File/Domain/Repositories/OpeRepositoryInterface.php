<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\FileVip;

interface OpeRepositoryInterface
{
    public function searchFilesPassToOpe(array $params): bool;
    public function searchHistoryPassToOpe(array $filters): LengthAwarePaginator;
}
