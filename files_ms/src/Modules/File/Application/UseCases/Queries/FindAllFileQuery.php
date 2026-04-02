<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindAllFileQuery implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct()
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator
    {
        return $this->fileRepository->findAll();
    }
}
