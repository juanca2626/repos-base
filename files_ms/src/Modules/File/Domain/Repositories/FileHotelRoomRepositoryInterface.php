<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator; 

interface FileHotelRoomRepositoryInterface
{ 
    public function updateAmountCost(int $id, array $params): array;   
    public function updateAmountCancellation(int $id, array $params): bool;        
    public function findById(int $id): array;
    public function updateStatus(int $id, int $status): bool;
    public function cancel(int $id): bool;
    public function updateConfirmationCode(int $id, string $code): array;
    
}
