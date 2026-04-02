<?php

namespace Src\Modules\File\Application\UseCases\Queries;
 
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileServiceByIdQuery implements QueryInterface
{
    private FileServiceRepositoryInterface $fileServiceRepository;

    public function __construct(private readonly int $fileServiceId)
    {
        $this->fileServiceRepository = app()->make(FileServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileServiceRepository->findById($this->fileServiceId);
    }
}
