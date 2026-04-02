<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class RemoveFileSerie implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly array $params)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): File
    {
        return $this->fileRepository->removeSerie($this->params);
    }
}
