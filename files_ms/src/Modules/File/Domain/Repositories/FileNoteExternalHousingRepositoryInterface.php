<?php

namespace Src\Modules\File\Domain\Repositories;

interface FileNoteExternalHousingRepositoryInterface{
    public function index(int $file): array;
    public function findById(int $file_id, int $id): array;
    public function store(int $file_id, array $params): array;
    public function update(int $file_id, int $id, array $params): array;
    public function delete(int $file_id, int $id): bool;
}
