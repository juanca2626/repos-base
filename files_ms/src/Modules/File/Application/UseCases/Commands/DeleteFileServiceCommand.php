<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileServiceCommand implements CommandInterface
{
    private FileServiceRepositoryInterface $repository;

    public function __construct(private readonly int $fileServiceId)
    {
        $this->repository = app()->make(FileServiceRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->delete($this->fileServiceId);
    }
}
