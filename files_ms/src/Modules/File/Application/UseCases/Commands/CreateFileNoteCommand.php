<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileNoteCommand implements CommandInterface{
    private FileNoteRepositoryInterface $repository;

    public function __construct(private readonly int $fileNoteId, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteRepositoryInterface::class);
    }

    public function execute() : array
    {
        return $this->repository->store($this->fileNoteId, $this->params);
    }
}
