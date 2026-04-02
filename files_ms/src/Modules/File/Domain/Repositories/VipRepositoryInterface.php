<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\Vip;

interface VipRepositoryInterface
{
    public function create(Vip $file_vip): Vip;
    public function findById(int $id): ?Vip;
    public function update(int $id, Vip $file): bool;
    public function delete(int $id): bool;
    public function searchAllVipsQuery(array $filters): int;
    public function searchVipsQuery(array $filters): LengthAwarePaginator;
}
