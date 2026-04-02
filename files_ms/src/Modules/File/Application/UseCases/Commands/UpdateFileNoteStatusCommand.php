<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileNoteStatusCommand implements CommandInterface{
    private FileNoteRepositoryInterface $repository;

    public function __construct(private readonly int $id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteRepositoryInterface::class);
    }

    public function execute() : bool
    {
        return $this->repository->updateStatus($this->id, $this->params);
    }
}
