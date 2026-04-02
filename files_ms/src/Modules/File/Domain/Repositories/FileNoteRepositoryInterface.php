<?php

namespace Src\Modules\File\Domain\Repositories;

interface FileNoteRepositoryInterface {
    public function getAllActive(int $fileId): array;
    public function getAllRequirement(int $fileId): array;
    public function index(int $fileId): array;
    public function store(int $fileNoteId, array $params): array;
    public function update(int $fileNoteId, int $note_id, array $params): array;
    public function updateStatus(int $id, array $params): bool;
    public function delete(int $fileNoteId, int $note_id, array $params): bool;
}
