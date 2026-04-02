<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface FileDebitNoteRepositoryInterface
{ 
    public function searchAll(int $file_id, array $params):array;
    public function create(int $file_id, array $params): bool;
    public function update(int $file_id, int $credit_note_id, array $params): bool;  
    public function delete(int $file_id, int $credit_note_id): bool; 
}
