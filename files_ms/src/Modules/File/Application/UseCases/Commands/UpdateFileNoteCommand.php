<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileNoteCommand implements CommandInterface{
    private FileNoteRepositoryInterface $repository;

    public function __construct(private readonly int $fileNoteId, private readonly int $note_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteRepositoryInterface::class);
    }

    public function execute() : array
    {
        return $this->repository->update($this->fileNoteId, $this->note_id, $this->params);
    }
}
