<?php

namespace Src\Modules\File\Domain\Repositories;

interface FileNoteGeneralRepositoryInterface {
    public function index(int $fileId): array;
    public function create(int $fileId, array $params): array;
    public function update(int $fileId, int $noteId, array $params): array;
}
