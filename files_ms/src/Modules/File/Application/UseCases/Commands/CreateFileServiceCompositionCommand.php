<?php

namespace Src\Modules\File\Application\UseCases\Commands;
  
use Src\Modules\File\Domain\Model\FileServiceComposition;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class CreateFileServiceCompositionCommand implements CommandInterface
{
    private FileServiceCompositionRepositoryInterface $repository;

    public function __construct(private readonly FileServiceComposition $fileServiceComposition)
    {
        $this->repository = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    public function execute(): FileServiceComposition
    {
        return $this->repository->create($this->fileServiceComposition);
    }
}
