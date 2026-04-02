<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileNoteExternalHousingRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileByIdExternalHousingQuery implements QueryInterface
{
    private FileNoteExternalHousingRepositoryInterface $fileRepository;

    public function __construct(private readonly int $file_id, private readonly int $id)
    {
        $this->fileRepository = app()->make(FileNoteExternalHousingRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->findById($this->file_id, $this->id);
    }
}
