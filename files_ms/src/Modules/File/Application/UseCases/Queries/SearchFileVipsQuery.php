<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileVipsQuery implements QueryInterface
{
    private FileVipRepositoryInterface $fileRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileRepository = app()->make(FileVipRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator
    {
        return $this->fileRepository->searchFileVipsQuery($this->filters);
    }
}
