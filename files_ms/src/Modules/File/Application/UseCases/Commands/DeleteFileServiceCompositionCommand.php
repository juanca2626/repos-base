<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileServiceCompositionCommand implements CommandInterface
{
    private FileServiceCompositionRepositoryInterface $repository;

    public function __construct(private readonly int $fileServiceCompositionId)
    {
        $this->repository = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->delete($this->fileServiceCompositionId);
    }
}
