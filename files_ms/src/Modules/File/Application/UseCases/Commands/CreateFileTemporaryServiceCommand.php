<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Modules\File\Domain\Repositories\FileTemporaryServiceRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileTemporaryServiceCommand implements CommandInterface
{
    private FileTemporaryServiceRepositoryInterface $repository;

    public function __construct(private readonly FileTemporaryService $fileTemporaryService)
    {
        $this->repository = app()->make(FileTemporaryServiceRepositoryInterface::class);
    }

    public function execute(): FileTemporaryService
    {
        return $this->repository->create($this->fileTemporaryService);
    }
}

