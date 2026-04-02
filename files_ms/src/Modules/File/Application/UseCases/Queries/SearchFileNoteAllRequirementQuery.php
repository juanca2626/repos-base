<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileNoteAllRequirementQuery implements QueryInterface {
    private FileNoteRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId)
    {
        $this->fileRepository = app()->make(FileNoteRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->getAllRequirement($this->fileId);
    }
}
