<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface FileStatementRepositoryInterface
{
    public function report(int $file_id): array;
    public function document_details_for_stella(int $file_id): array;
    public function createStatement(int $file_id , array $params): bool;
    public function updateStatement(int $file_id , array $params): bool;  
    public function updateManuallyStatement(int $file_id , array $params): bool;      
    public function details(int $file_id): array;
    public function blocked(): array;
    public function desBlocked(array $params): bool;
    
}
