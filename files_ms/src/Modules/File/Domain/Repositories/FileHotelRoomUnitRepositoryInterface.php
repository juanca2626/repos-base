<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator; 

interface FileHotelRoomUnitRepositoryInterface
{ 
    public function updateStatus(int $id, int $status): bool;    
    public function findById(int $id): array;
    public function updateAmountCost(int $id, float $newAmountCost): bool;  
    public function updateAmountSale(int $id, float $newAmountCost): bool;  
    public function updateConfirmationCode(int $id, string $code): array; 
    public function updateRqWl(int $id, array $params): bool; 
    public function updateWlCode(int $id, string $code): bool; 
    
}
