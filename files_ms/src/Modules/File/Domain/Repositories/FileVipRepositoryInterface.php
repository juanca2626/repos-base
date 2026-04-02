<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\FileVip;

interface FileVipRepositoryInterface
{
    public function create(FileVip $fileVip): FileVip;
    public function findById(int $id): ?FileVip;
    public function update(int $id, FileVip $fileVip): bool;
    public function delete(int $file_id, int $vip_id): bool;
    public function searchFileVipsQuery(array $filters): LengthAwarePaginator;
}
