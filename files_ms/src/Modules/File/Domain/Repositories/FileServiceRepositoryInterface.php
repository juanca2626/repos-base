<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\ValueObjects\FileService\StartTime; 

interface FileServiceRepositoryInterface
{
    public function create(FileService $fileService): FileService; 
    public function updateSchedule(int $id, array $params): array;
    public function updateDate(int $id, string $params): array;
    public function updateAmountCost(int $id, array $params): array;
    public function findById(int $id): array;
    public function cancel(int $id): bool;
    public function delete(int $id): bool;
    public function updateTotalAmountCost(int $id): bool;
    
}
