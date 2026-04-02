<?php

namespace Src\Modules\File\Application\UseCases\Queries;
use Src\Modules\File\Domain\Repositories\FileNoteExternalHousingRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileNoteExternalHousingQuery implements QueryInterface{
    private FileNoteExternalHousingRepositoryInterface $repository;
    public function __construct(private readonly int $file_id)
    {
        $this->repository = app()->make(FileNoteExternalHousingRepositoryInterface::class);
    }
    /**
     * @return mixed
     */
    public function handle(): array {
        return $this->repository->index($this->file_id);
    }
}
