<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileDebitNoteRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileDebitNoteCommand implements CommandInterface
{
    private FileDebitNoteRepositoryInterface $repository;

    public function __construct(private readonly int $file, private readonly int $debit_note_id ,private readonly array $params)
    {
        $this->repository = app()->make(FileDebitNoteRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->update($this->file, $this->debit_note_id, $this->params);
    }
}
