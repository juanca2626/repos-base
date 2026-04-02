<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileAmountReasonRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileAmountReasonsQuery implements QueryInterface
{
    private FileAmountReasonRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileAmountReasonRepository = app()->make(FileAmountReasonRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->searchAll($this->filters);
    }
}
