<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileNoteGeneralRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileNoteGeneralCommand implements CommandInterface{

    private FileNoteGeneralRepositoryInterface $repository;
    public function __construct(private readonly int $file_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteGeneralRepositoryInterface::class);
    }

    public function execute(): array
    {
        return $this->repository->create($this->file_id, $this->params);
    }
}
