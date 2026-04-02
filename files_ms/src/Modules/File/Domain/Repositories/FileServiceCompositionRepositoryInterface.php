<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\FileServiceComposition;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\StartTime;
use Illuminate\Support\Collection;

interface FileServiceCompositionRepositoryInterface
{
    public function create(FileServiceComposition $fileService): FileServiceComposition; 
    public function updateSchedule(int $id, array $params): array;
    public function updateStatus(int $id, int $status): bool; 
    public function findById(int $id): array; 
    public function updateAmountCost(int $id, float $newAmountCost): bool;  
    public function delete(int $id): bool;
    public function updateConfirmationCode(int $id, string $code): array;
    public function findServicesByFileId(int $id): array; 
    public function findServicesByCompositionId(int $id): array; 
    public function updateSendNotification(int $id): bool;  
    

}
