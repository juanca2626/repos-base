<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\FileVip;

interface FilePassengerModifyPaxRepositoryInterface
{
    public function create(int $fileId): bool; 
    public function reset(int $fileId): bool;     
    public function update(int $id, array $dataPassengers): bool; 
    public function searchAll(int $id): array;

}
