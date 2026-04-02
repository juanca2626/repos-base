<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileCreditNoteRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileCreditNoteCommand implements CommandInterface
{
    private FileCreditNoteRepositoryInterface $repository;

    public function __construct(private readonly int $file ,private readonly array $params)
    {
        $this->repository = app()->make(FileCreditNoteRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->create($this->file, $this->params);
    }
}
