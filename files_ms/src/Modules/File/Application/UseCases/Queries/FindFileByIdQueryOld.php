<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileByIdQueryOld implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $id, private readonly string $type = 'file')
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): File|array
    {
        if($this->type == 'file')
        {
            return $this->fileRepository->findById($this->id);
        }
        if($this->type == 'array')
        {
            return $this->fileRepository->findByIdToArray($this->id);
        }
        
    }
}
