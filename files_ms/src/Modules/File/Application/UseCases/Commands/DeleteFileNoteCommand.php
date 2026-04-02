<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileNoteCommand implements CommandInterface
{
    private FileNoteRepositoryInterface $repository;

    public function __construct(private readonly int $file_id,private readonly int $note_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteRepositoryInterface::class);
    }
    public function execute(): bool{
        return $this->repository->delete($this->file_id,$this->note_id, $this->params);
    }
}
