<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Repositories\FileNoteExternalHousingRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileNoteExternalHousingCommand implements CommandInterface{
    private FileNoteExternalHousingRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteExternalHousingRepositoryInterface::class);
    }

    public function execute() : array
    {
        return $this->repository->store($this->file_id, $this->params);
    }
}
