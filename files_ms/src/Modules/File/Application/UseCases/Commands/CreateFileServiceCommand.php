<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileServiceCommand implements CommandInterface
{
    private FileServiceRepositoryInterface $repository;

    public function __construct(private readonly FileService $fileService)
    {
        $this->repository = app()->make(FileServiceRepositoryInterface::class);
    }

    public function execute(): FileService
    {
        return $this->repository->create($this->fileService);
    }
}
