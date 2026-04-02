<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Repositories\FileNoteExternalHousingRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileNoteExternalHousingCommand implements CommandInterface{
    private FileNoteExternalHousingRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly int $id)
    {
        $this->repository = app()->make(FileNoteExternalHousingRepositoryInterface::class);
    }

    public function execute() : bool
    {
        return $this->repository->delete($this->file_id, $this->id);
    }
}
