<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileServiceComposition;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileServiceCompositionQuery implements QueryInterface
{
    private FileServiceCompositionRepositoryInterface $fileServiceComposition;

    public function __construct(private readonly int $id)
    {
        $this->fileServiceComposition = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): FileServiceComposition
    {
        return $this->fileServiceComposition->findById($this->id);
    }
}
