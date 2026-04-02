<?php

namespace Src\Modules\File\Domain\Repositories;


interface FileServiceUnitRepositoryInterface
{    
    public function updateStatus(int $id, int $status): bool;
    public function findById(int $id): array; 
    public function updateAmountCost(int $id, float $newAmountCost): bool;  
    public function updateConfirmationCode(int $id, string $code): bool; 
    public function updateRqWl(int $id, array $params): bool; 
    public function updateWlCode(int $id, string $code): bool; 
    
}
