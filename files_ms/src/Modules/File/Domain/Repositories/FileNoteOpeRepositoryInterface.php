<?php

namespace Src\Modules\File\Domain\Repositories;

interface FileNoteOpeRepositoryInterface {
    public function listForFile(int $fileNumber): array;
    public function listForService(int $fileNumber): array;
}
