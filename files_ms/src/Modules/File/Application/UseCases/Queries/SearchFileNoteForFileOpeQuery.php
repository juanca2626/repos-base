<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileNoteOpeRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileNoteForFileOpeQuery implements QueryInterface {
    private FileNoteOpeRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileNumber)
    {
        $this->fileRepository = app()->make(FileNoteOpeRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->listForFile($this->fileNumber);
    }
}
