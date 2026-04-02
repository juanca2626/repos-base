<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator; 
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;

interface FilePassengerRepositoryInterface
{
    public function create(int $fileId): bool; 
    public function updateAll(int $id, array $dataPassengers): bool;     
    public function findById(int $id): array;
    public function searchAll(int $id): array;
    public function updateAccommodation(int $id, array $dataPassengers): bool;     
    
}
